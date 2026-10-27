<?php
/**
 * The model file of repohook module of ZenTaoPMS (WenYinOS community build).
 *
 * 代码平台 Webhook 接收端（首版只处理 push 事件）：
 * 校验平台签名 → 解析 commit 注释里的对象编号 → 复用 git 模块的 parseComment / saveAction2PMS 写入 action。
 * 与现有 git/svn 的 cron 拉取式并存：拉取式留给内网无外网回调的场景，Webhook 留给公网托管场景。
 * 注意：现有 parseComment 只提取对象 ID、不解析动作动词，saveAction2PMS 只写 gitcommited action、
 * 不改变对象状态——本模块同样只写 action，不做状态流转。
 *
 * @package repohook
 */
class repohookModel extends model
{
    /**
     * 支持的代码平台。
     *
     * @access public
     * @return array
     */
    public function getProviders()
    {
        return array('github' => 'GitHub', 'gitlab' => 'GitLab', 'gitea' => 'Gitea');
    }

    /**
     * 按 token 取 hook。
     *
     * @param  string $token
     * @access public
     * @return object|bool
     */
    public function getByToken($token)
    {
        if($token === '') return false;
        return $this->dao->select('*')->from(TABLE_REPOHOOK)
            ->where('token')->eq($token)
            ->andWhere('deleted')->eq('0')
            ->fetch();
    }

    /**
     * Get a hook by id.
     *
     * @param  int    $id
     * @access public
     * @return object|bool
     */
    public function getByID($id)
    {
        return $this->dao->select('*')->from(TABLE_REPOHOOK)->where('id')->eq((int)$id)->andWhere('deleted')->eq('0')->fetch();
    }

    /**
     * Get hook list.
     *
     * @param  string $orderBy
     * @param  object $pager
     * @access public
     * @return array
     */
    public function getList($orderBy = 'id_desc', $pager = null)
    {
        return $this->dao->select('*')->from(TABLE_REPOHOOK)
            ->where('deleted')->eq('0')
            ->orderBy($orderBy)
            ->page($pager)
            ->fetchAll('id');
    }

    /**
     * Get log list of a hook.
     *
     * @param  int    $hookID
     * @param  string $orderBy
     * @param  object $pager
     * @access public
     * @return array
     */
    public function getLogList($hookID, $orderBy = 'id_desc', $pager = null)
    {
        return $this->dao->select('*')->from(TABLE_REPOHOOKLOG)
            ->where('hookID')->eq((int)$hookID)
            ->orderBy($orderBy)
            ->page($pager)
            ->fetchAll('id');
    }

    /**
     * Create a hook.
     *
     * @access public
     * @return bool
     */
    public function create()
    {
        $hook = fixer::input('post')
            ->setDefault('provider', 'github')
            ->setDefault('status', 'normal')
            ->setDefault('product', 0)
            ->add('token', $this->generateToken())
            ->add('createdBy', $this->app->user->account)
            ->add('createdDate', helper::now())
            ->add('editedBy', '')
            ->add('editedDate', helper::now())
            ->remove('uid')
            ->get();

        $this->dao->insert(TABLE_REPOHOOK)->data($hook)
            ->batchCheck($this->config->repohook->create->requiredFields, 'notempty')
            ->autoCheck()
            ->exec();
        return !dao::isError();
    }

    /**
     * Update a hook.
     *
     * @param  int    $id
     * @access public
     * @return bool
     */
    public function update($id)
    {
        $hook = fixer::input('post')
            ->setDefault('provider', 'github')
            ->setDefault('status', 'normal')
            ->setDefault('product', 0)
            ->add('editedBy', $this->app->user->account)
            ->add('editedDate', helper::now())
            ->remove('uid')
            ->get();

        $this->dao->update(TABLE_REPOHOOK)->data($hook)
            ->batchCheck($this->config->repohook->edit->requiredFields, 'notempty')
            ->autoCheck()
            ->where('id')->eq((int)$id)
            ->exec();
        return !dao::isError();
    }

    /**
     * Generate a unique token for the receive URL.
     *
     * @access public
     * @return string
     */
    public function generateToken()
    {
        return md5(uniqid((string)mt_rand(), true));
    }

    /**
     * Save a receive log (failures are swallowed by design).
     *
     * @param  int    $hookID
     * @param  string $event
     * @param  string $payload
     * @param  string $result
     * @access public
     * @return void
     */
    public function saveLog($hookID, $event, $payload, $result)
    {
        $log = new stdclass();
        $log->hookID      = (int)$hookID;
        $log->event       = (string)$event;
        $log->payload     = (string)$payload;
        $log->result      = (string)$result;
        $log->createdDate = helper::now();

        $this->dao->insert(TABLE_REPOHOOKLOG)->data($log)->exec();
    }

    /**
     * Receive one webhook request.
     *
     * @param  array  $get      $_GET
     * @param  array  $server   $_SERVER
     * @param  string $rawBody  raw request body
     * @access public
     * @return array
     */
    public function receive($get, $server, $rawBody)
    {
        $token = isset($get['token']) ? trim((string)$get['token']) : '';
        if($token === '') return array('result' => 'fail', 'message' => 'missing token');

        $hook = $this->getByToken($token);
        if(!$hook) return array('result' => 'fail', 'message' => 'unknown hook');
        if($hook->status != 'normal') return array('result' => 'fail', 'message' => 'hook is closed');

        $event = $this->detectEvent($hook->provider, $server);

        if(!$this->verifySignature($hook, $server, $rawBody))
        {
            $this->saveLog($hook->id, $event, $rawBody, 'signature verification failed');
            return array('result' => 'fail', 'message' => 'invalid signature');
        }

        /* 首版只处理 push；其他事件记录后忽略。 */
        if($event != 'push')
        {
            $this->saveLog($hook->id, $event, $rawBody, 'ignored (only push is handled)');
            return array('result' => 'ok', 'message' => 'ignored');
        }

        $result = $this->handlePush($hook, $rawBody);
        $this->saveLog($hook->id, $event, $rawBody, $result);
        return array('result' => 'ok', 'message' => $result);
    }

    /**
     * Detect the event name from provider headers.
     *
     * @param  string $provider
     * @param  array  $server
     * @access private
     * @return string
     */
    private function detectEvent($provider, $server)
    {
        if($provider == 'gitlab')
        {
            /* GitLab: X-Gitlab-Event: Push Hook / Tag Push Hook / Merge Request Hook ... */
            $event = strtolower($this->header($server, 'X-Gitlab-Event'));
            return strpos($event, 'push') !== false ? 'push' : trim(str_replace(' hook', '', $event));
        }

        /* GitHub / Gitea: X-GitHub-Event / X-Gitea-Event，值为 push / ping / ... */
        $event = $this->header($server, 'X-GitHub-Event');
        if($event === '') $event = $this->header($server, 'X-Gitea-Event');
        return strtolower(trim($event));
    }

    /**
     * Read a request header from $_SERVER.
     *
     * @param  array  $server
     * @param  string $name   e.g. X-Hub-Signature-256
     * @access private
     * @return string
     */
    private function header($server, $name)
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return isset($server[$key]) ? (string)$server[$key] : '';
    }

    /**
     * Verify the platform signature. Empty secret always fails.
     *
     * @param  object $hook
     * @param  array  $server
     * @param  string $rawBody
     * @access private
     * @return bool
     */
    private function verifySignature($hook, $server, $rawBody)
    {
        $secret = (string)$hook->secret;
        if($secret === '') return false;

        if($hook->provider == 'gitlab')
        {
            return hash_equals($secret, $this->header($server, 'X-Gitlab-Token'));
        }

        $signature = $this->header($server, 'X-Hub-Signature-256');
        if($signature === '') $signature = $this->header($server, 'X-Hub-Signature');
        if($signature === '') return false;

        if(strpos($signature, 'sha256=') === 0)
        {
            $expected = 'sha256=' . hash_hmac('sha256', $rawBody, $secret);
        }
        elseif(strpos($signature, 'sha1=') === 0)
        {
            $expected = 'sha1=' . hash_hmac('sha1', $rawBody, $secret);
        }
        else
        {
            return false;
        }

        return hash_equals($expected, $signature);
    }

    /**
     * Handle a push payload: parse each commit and write actions via the git module.
     *
     * @param  object $hook
     * @param  string $rawBody
     * @access private
     * @return string
     */
    private function handlePush($hook, $rawBody)
    {
        $payload = json_decode($rawBody, true);
        if(!is_array($payload) or empty($payload['commits']) or !is_array($payload['commits'])) return 'no commits in payload';

        $this->loadModel('git');

        $handled = 0;
        foreach($payload['commits'] as $commit)
        {
            if(!is_array($commit)) continue;

            $msg = isset($commit['message']) ? (string)$commit['message'] : '';
            if($msg === '') continue;

            $objects = $this->git->parseComment($msg);
            if(!$objects) continue;

            $log = new stdclass();
            $log->author   = isset($commit['author']['name']) ? (string)$commit['author']['name'] : '';
            $log->date     = !empty($commit['timestamp']) ? date('Y-m-d H:i:s', strtotime($commit['timestamp'])) : helper::now();
            $log->msg      = $msg;
            $log->revision = isset($commit['id']) ? (string)$commit['id'] : '';
            /* webhook 没有本地仓库，不做 diff 链接（createActionChanges 见空 files 直接返回空）。 */
            $log->files    = array();

            $this->git->saveAction2PMS($objects, $log);
            $handled++;
        }

        return $handled ? "handled {$handled} commit(s)" : 'no commit matched object pattern';
    }
}

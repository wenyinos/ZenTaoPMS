<?php
/**
 * The control file of repohook module of ZenTaoPMS (WenYinOS community build).
 *
 * @package repohook
 */
class repohook extends control
{
    /**
     * Browse hooks.
     *
     * @param  string $orderBy
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function browse($orderBy = 'id_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        $this->app->loadClass('pager', $static = true);
        $pager = new pager($recTotal, $recPerPage, $pageID);

        $this->view->title      = $this->lang->repohook->common . $this->lang->colon . $this->lang->repohook->browse;
        $this->view->position[] = $this->lang->repohook->common;
        $this->view->hooks      = $this->repohook->getList($orderBy, $pager);
        $this->view->providers  = $this->repohook->getProviders();
        $this->view->orderBy    = $orderBy;
        $this->view->pager      = $pager;
        $this->display();
    }

    /**
     * Create a hook.
     *
     * @access public
     * @return void
     */
    public function create()
    {
        if($_POST)
        {
            $this->repohook->create();
            if(dao::isError()) $this->send(array('result' => 'fail', 'message' => dao::getError()));
            $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => inlink('browse')));
        }

        $this->view->title      = $this->lang->repohook->common . $this->lang->colon . $this->lang->repohook->create;
        $this->view->position[] = html::a(inlink('browse'), $this->lang->repohook->common);
        $this->view->position[] = $this->lang->repohook->create;
        $this->view->providers  = $this->repohook->getProviders();
        $this->view->products   = $this->loadModel('product')->getPairs();
        $this->view->baseURL    = common::getSysURL() . $this->config->webRoot . 'repohook.php?token=';
        $this->display();
    }

    /**
     * Edit a hook.
     *
     * @param  int    $id
     * @access public
     * @return void
     */
    public function edit($id)
    {
        $hook = $this->repohook->getByID($id);
        if(!$hook) die(js::error($this->lang->notFound) . js::locate('back'));

        if($_POST)
        {
            $this->repohook->update($id);
            if(dao::isError()) $this->send(array('result' => 'fail', 'message' => dao::getError()));
            $this->send(array('result' => 'success', 'message' => $this->lang->saveSuccess, 'locate' => inlink('browse')));
        }

        $this->view->title      = $this->lang->repohook->common . $this->lang->colon . $this->lang->repohook->edit;
        $this->view->position[] = html::a(inlink('browse'), $this->lang->repohook->common);
        $this->view->position[] = $this->lang->repohook->edit;
        $this->view->hook       = $hook;
        $this->view->providers  = $this->repohook->getProviders();
        $this->view->products   = $this->loadModel('product')->getPairs();
        $this->view->baseURL    = common::getSysURL() . $this->config->webRoot . 'repohook.php?token=';
        $this->display();
    }

    /**
     * Delete a hook.
     *
     * @param  int    $id
     * @param  string $confirm
     * @access public
     * @return void
     */
    public function delete($id, $confirm = 'no')
    {
        if($confirm == 'no') die(js::confirm($this->lang->repohook->confirmDelete, inlink('delete', "id=$id&confirm=yes")));

        /* 复用框架 model::delete($table, $id) 的软删实现（置 deleted=1 并写 action）。 */
        $this->repohook->delete(TABLE_REPOHOOK, $id);
        die(js::reload('parent'));
    }

    /**
     * Receive logs of a hook.
     *
     * @param  int    $id
     * @param  string $orderBy
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function log($id, $orderBy = 'id_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        $hook = $this->repohook->getByID($id);
        if(!$hook) die(js::error($this->lang->notFound) . js::locate('back'));

        $this->app->loadClass('pager', $static = true);
        $pager = new pager($recTotal, $recPerPage, $pageID);

        $this->view->title      = $this->lang->repohook->common . $this->lang->colon . $this->lang->repohook->log;
        $this->view->position[] = html::a(inlink('browse'), $this->lang->repohook->common);
        $this->view->position[] = $this->lang->repohook->log;
        $this->view->hook       = $hook;
        $this->view->logs       = $this->repohook->getLogList($id, $orderBy, $pager);
        $this->view->orderBy    = $orderBy;
        $this->view->pager      = $pager;
        $this->display();
    }
}

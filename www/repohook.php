<?php
/**
 * The repo hook router file of ZenTaoPMS (WenYinOS community build).
 *
 * 代码平台 Webhook 的公开接收入口，**不检查登录态**：
 * 由 URL 上的 token 定位配置，再用该配置的 secret 校验平台签名（GitHub/Gitea 走
 * X-Hub-Signature-256，GitLab 走 X-Gitlab-Token）。签名不过一律拒绝并记日志。
 * 只处理 push 事件，解析 commit 注释中的对象编号并复用 git 模块写入 action。
 *
 * @package repohook
 */
/* Set the error reporting. */
error_reporting(0);

/* Start output buffer. */
ob_start();

/* Load the framework. */
include '../framework/router.class.php';
include '../framework/control.class.php';
include '../framework/model.class.php';
include '../framework/helper.class.php';

/* Log the time and define the run mode. */
$startTime = getTime();

/* Instance the app. */
$app = router::createApp('pms', dirname(dirname(__FILE__)), 'router');

/* installed or not. */
if(!isset($config->installed) or !$config->installed) die('');

/* Run the app. */
$common = $app->loadCommon();

$result = $common->loadModel('repohook')->receive($_GET, $_SERVER, file_get_contents('php://input'));

header('Content-Type: application/json; charset=utf-8');
echo json_encode($result);

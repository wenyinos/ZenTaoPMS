<?php
/**
 * The badge router file of ZenTaoPMS (WenYinOS community build).
 *
 * 公开只读的 SVG 徽章入口，供 README 等外部页面嵌入，**不检查登录态**
 * （与 index.php 的差异：不调用 checkPriv / parseRequest，直接调用 badge 模型）。
 * 仅输出聚合数字与版本名，故可安全匿名访问；可在 config/my.php 里关闭。
 *
 * @package badge
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
$badge  = $common->loadModel('badge');

header('Content-Type: image/svg+xml; charset=utf-8');
header('Cache-Control: public, max-age=300');

echo $badge->render($_GET);

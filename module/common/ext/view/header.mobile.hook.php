<?php
/* wenyinos 禅道移动端模板层注入（header.lite.html.php 内，$themeRoot 已定义；主题无关共享路径，三个主题通用） */
/* header.html.php 也会通过 header.*.hook.php 再引入一次，用常量保证样式只输出一次。 */
if(defined('WENYIN_MOBILE_CSS_LOADED')) return;
define('WENYIN_MOBILE_CSS_LOADED', true);
/* 带文件指纹：样式修订后无需改版本号即可刷新浏览器缓存（与 compat.css 同机制） */
$mobileCssFile = $this->app->getWwwRoot() . 'theme/mobile.css';
$mobileCssTs   = file_exists($mobileCssFile) ? filemtime($mobileCssFile) : 0;
css::import($themeRoot . 'mobile.css?t=' . $mobileCssTs);

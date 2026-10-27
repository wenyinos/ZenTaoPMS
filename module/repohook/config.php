<?php
/**
 * The config file of repohook module of ZenTaoPMS (WenYinOS community build).
 *
 * @package repohook
 */
$config->repohook->create = new stdclass();
$config->repohook->create->requiredFields = 'name,secret';

$config->repohook->edit = new stdclass();
$config->repohook->edit->requiredFields = 'name,secret';

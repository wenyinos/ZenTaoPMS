<?php
$lang->repohook->common   = '代码回调';
$lang->repohook->browse   = '回调列表';
$lang->repohook->create   = '添加回调';
$lang->repohook->edit     = '编辑回调';
$lang->repohook->delete   = '删除回调';
$lang->repohook->log      = '接收日志';

$lang->repohook->id          = 'ID';
$lang->repohook->name        = '名称';
$lang->repohook->provider    = '代码平台';
$lang->repohook->token       = '回调地址令牌';
$lang->repohook->secret      = '签名密钥';
$lang->repohook->product     = "关联{$lang->productCommon}";
$lang->repohook->status      = '状态';
$lang->repohook->createdBy   = '由谁创建';
$lang->repohook->createdDate = '创建时间';
$lang->repohook->editedBy    = '最后编辑';
$lang->repohook->editedDate  = '编辑时间';

$lang->repohook->event       = '事件';
$lang->repohook->payload     = '请求内容';
$lang->repohook->result      = '处理结果';
$lang->repohook->date        = '接收时间';

$lang->repohook->statusList['normal'] = '正常';
$lang->repohook->statusList['closed'] = '停用';

$lang->repohook->confirmDelete = '您确认要删除该回调吗？';

$lang->repohook->note = new stdclass();
$lang->repohook->note->secret  = '与代码平台侧填写的 Secret 保持一致；留空则所有请求都会被拒绝。';
$lang->repohook->note->payload = '请把下方地址填到代码平台的 Webhook 配置里（内容类型选 application/json）。';
$lang->repohook->note->onlyPush = '当前版本只处理 push 事件；其他事件会被记录但忽略。';

$lang->repohook->tip = new stdclass();
$lang->repohook->tip->commitPattern = '提交注释中写 task #12 / bug #34 / story #56 即可关联到对应对象（与既有 git/svn 拉取式同一套解析规则，只记录关联动作、不改变对象状态）。';

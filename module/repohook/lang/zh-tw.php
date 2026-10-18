<?php
$lang->repohook->common   = '代碼回調';
$lang->repohook->browse   = '回調列表';
$lang->repohook->create   = '添加回調';
$lang->repohook->edit     = '編輯回調';
$lang->repohook->delete   = '刪除回調';
$lang->repohook->log      = '接收日誌';

$lang->repohook->id          = 'ID';
$lang->repohook->name        = '名稱';
$lang->repohook->provider    = '代碼平台';
$lang->repohook->token       = '回調地址令牌';
$lang->repohook->secret      = '簽名密鑰';
$lang->repohook->product     = "關聯{$lang->productCommon}";
$lang->repohook->status      = '狀態';
$lang->repohook->createdBy   = '由誰創建';
$lang->repohook->createdDate = '創建時間';
$lang->repohook->editedBy    = '最後編輯';
$lang->repohook->editedDate  = '編輯時間';

$lang->repohook->event       = '事件';
$lang->repohook->payload     = '請求內容';
$lang->repohook->result      = '處理結果';
$lang->repohook->date        = '接收時間';

$lang->repohook->statusList['normal'] = '正常';
$lang->repohook->statusList['closed'] = '停用';

$lang->repohook->confirmDelete = '您確認要刪除該回調嗎？';

$lang->repohook->note = new stdclass();
$lang->repohook->note->secret  = '與代碼平台側填寫的 Secret 保持一致；留空則所有請求都會被拒絕。';
$lang->repohook->note->payload = '請把下方地址填到代碼平台的 Webhook 配置裏（內容類型選 application/json）。';
$lang->repohook->note->onlyPush = '當前版本只處理 push 事件；其他事件會被記錄但忽略。';

$lang->repohook->tip = new stdclass();
$lang->repohook->tip->commitPattern = '提交註釋中寫 task #12 / bug #34 / story #56 即可關聯到對應對象（與既有 git/svn 拉取式同一套解析規則，只記錄關聯動作、不改變對象狀態）。';

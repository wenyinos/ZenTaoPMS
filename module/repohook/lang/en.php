<?php
$lang->repohook->common   = 'Repo Hook';
$lang->repohook->browse   = 'Hook List';
$lang->repohook->create   = 'Create Hook';
$lang->repohook->edit     = 'Edit Hook';
$lang->repohook->delete   = 'Delete Hook';
$lang->repohook->log      = 'Receive Log';

$lang->repohook->id          = 'ID';
$lang->repohook->name        = 'Name';
$lang->repohook->provider    = 'Provider';
$lang->repohook->token       = 'Receive Token';
$lang->repohook->secret      = 'Secret';
$lang->repohook->product     = $lang->productCommon;
$lang->repohook->status      = 'Status';
$lang->repohook->createdBy   = 'Created By';
$lang->repohook->createdDate = 'Created Date';
$lang->repohook->editedBy    = 'Edited By';
$lang->repohook->editedDate  = 'Edited Date';

$lang->repohook->event       = 'Event';
$lang->repohook->payload     = 'Payload';
$lang->repohook->result      = 'Result';
$lang->repohook->date        = 'Received At';

$lang->repohook->statusList['normal'] = 'Normal';
$lang->repohook->statusList['closed'] = 'Closed';

$lang->repohook->confirmDelete = 'Are you sure to delete this hook?';

$lang->repohook->note = new stdclass();
$lang->repohook->note->secret  = 'Must match the Secret configured on the code platform; an empty secret rejects every request.';
$lang->repohook->note->payload = 'Fill the address below into the code platform webhook settings (content type: application/json).';
$lang->repohook->note->onlyPush = 'Only push events are handled in this version; other events are logged and ignored.';

$lang->repohook->tip = new stdclass();
$lang->repohook->tip->commitPattern = 'Write task #12 / bug #34 / story #56 in the commit message to link the object (same parser as the git/svn pull mode; only records the action, never changes object status).';

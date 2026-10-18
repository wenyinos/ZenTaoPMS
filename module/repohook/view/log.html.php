<?php
/**
 * The log view file of repohook module of ZenTaoPMS (WenYinOS community build).
 *
 * @package repohook
 */
?>
<?php include '../../common/view/header.html.php';?>
<div id='mainMenu' class='clearfix'>
  <div class='btn-toolbar pull-left'>
    <?php echo html::a(inlink('browse'), '<i class="icon icon-back"></i> ' . $lang->repohook->common, '', "class='btn btn-link'");?>
    <span class='btn btn-link btn-active-text'><span class='text'><?php echo $lang->repohook->log;?></span></span>
  </div>
</div>
<div id='mainContent'>
  <form class='main-table' method='post'>
    <table class='table has-sort-head table-fixed'>
      <thead>
        <tr>
          <th class='w-60px'><?php echo $lang->repohook->id;?></th>
          <th class='w-140px'><?php echo $lang->repohook->event;?></th>
          <th class='w-160px'><?php echo $lang->repohook->date;?></th>
          <th class='w-240px'><?php echo $lang->repohook->result;?></th>
          <th><?php echo $lang->repohook->payload;?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($logs as $id => $log):?>
        <tr>
          <td class='text-center'><?php echo $id;?></td>
          <td class='text-center'><?php echo $log->event;?></td>
          <td class='text-center'><?php echo $log->createdDate;?></td>
          <td class='text'><?php echo $log->result;?></td>
          <td class='text'><code style='word-break:break-all;'><?php echo helper::substr(htmlspecialchars($log->payload, ENT_QUOTES, 'UTF-8'), 200);?></code></td>
        </tr>
        <?php endforeach;?>
      </tbody>
    </table>
    <?php if($logs):?>
    <div class='table-footer'><?php $pager->show('right', 'pagerjs');?></div>
    <?php endif;?>
  </form>
</div>
<?php include '../../common/view/footer.html.php';?>

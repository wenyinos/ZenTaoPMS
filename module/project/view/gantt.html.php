<?php
/**
 * The gantt view file of project module of ZenTaoPMS (WenYinOS community build).
 *
 * 只读甘特图：以 eCharts 自定义系列渲染任务的起止日期与完成进度，不提供拖拽改期。
 *
 * @package project
 */
?>
<?php include '../../common/view/header.html.php';?>
<?php
/* 组装给前端的数据：只保留能在时间轴上定位的字段。 */
$ganttTasks = array();
foreach($tasks as $task)
{
    $progress = 0;
    $total    = (float)$task->consumed + (float)$task->left;
    if($total > 0) $progress = (int)round((float)$task->consumed / $total * 100);
    if($task->status == 'done' or $task->status == 'closed') $progress = 100;

    $ganttTasks[] = array(
        'name'       => $task->name,
        'start'      => $task->estStarted,
        'end'        => $task->deadline,
        'status'     => $task->status,
        'statusName' => zget($lang->task->statusList, $task->status),
        'assignedTo' => $task->assignedTo ? zget($users, $task->assignedTo) : '',
        'progress'   => $progress,
    );
}
js::set('ganttTasks', $ganttTasks);
?>
<div id='mainMenu' class='clearfix'>
  <div class='btn-toolbar pull-left'>
    <?php echo html::a(inlink('task', "projectID={$project->id}"), '<i class="icon icon-list"></i> ' . $lang->project->task, '', "class='btn btn-link'");?>
    <span class='btn btn-link btn-active-text'><span class='text'><?php echo $lang->project->gantt;?></span></span>
  </div>
</div>
<div id='mainContent' class='main-content'>
  <div class='main-header'>
    <h2><?php echo $lang->project->gantt;?></h2>
  </div>
  <?php if(empty($ganttTasks)):?>
  <div class='table-empty-tip'>
    <p><span class='text-muted'><?php echo $lang->project->ganttEmpty;?></span></p>
  </div>
  <?php else:?>
  <div class='cell'>
    <div id='ganttChart' style='width:100%;height:<?php echo max(360, count($ganttTasks) * 30 + 120);?>px;'></div>
  </div>
  <?php endif;?>
</div>
<?php include '../../common/view/footer.html.php';?>

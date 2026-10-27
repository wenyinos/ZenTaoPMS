<?php
/**
 * The browse view file of repohook module of ZenTaoPMS (WenYinOS community build).
 *
 * @package repohook
 */
?>
<?php include '../../common/view/header.html.php';?>
<?php js::set('confirmDelete', $lang->repohook->confirmDelete);?>
<div id='mainMenu' class='clearfix'>
  <div class='btn-toolbar pull-left'>
    <span class='btn btn-link btn-active-text'><span class='text'><?php echo $lang->repohook->browse;?></span></span>
  </div>
  <div class='btn-toolbar pull-right'>
    <?php echo html::a(inlink('create'), '<i class="icon icon-plus"></i> ' . $lang->repohook->create, '', "class='btn btn-primary'");?>
  </div>
</div>
<div id='mainContent'>
  <div class='cell'><p class='text-muted'><?php echo $lang->repohook->note->onlyPush . ' ' . $lang->repohook->tip->commitPattern;?></p></div>
  <form class='main-table' id='ajaxForm' method='post'>
    <table id='repohookList' class='table has-sort-head table-fixed'>
      <thead>
        <tr>
          <?php $vars = "orderBy=%s&recTotal={$pager->recTotal}&recPerPage={$pager->recPerPage}&pageID={$pager->pageID}";?>
          <th class='w-60px'><?php common::printOrderLink('id', $orderBy, $vars, $lang->repohook->id);?></th>
          <th class='w-120px'><?php common::printOrderLink('provider', $orderBy, $vars, $lang->repohook->provider);?></th>
          <th class='w-200px text-left'><?php common::printOrderLink('name', $orderBy, $vars, $lang->repohook->name);?></th>
          <th><?php echo $lang->repohook->token;?></th>
          <th class='w-80px text-center'><?php echo $lang->repohook->status;?></th>
          <th class='c-actions-3'><?php echo $lang->actions;?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($hooks as $id => $hook):?>
        <tr>
          <td class='text-center'><?php echo $id;?></td>
          <td class='text-center'><?php echo zget($providers, $hook->provider, $hook->provider);?></td>
          <td class='text' title='<?php echo $hook->name;?>'><?php echo $hook->name;?></td>
          <td class='text'><code>repohook.php?token=<?php echo $hook->token;?></code></td>
          <td class='text-center'><?php echo zget($lang->repohook->statusList, $hook->status);?></td>
          <td class='c-actions'>
            <?php
            common::printIcon('repohook', 'log', "id=$id", '', 'list', 'file-text');
            common::printIcon('repohook', 'edit', "id=$id", '', 'list');
            if(common::hasPriv('repohook', 'delete'))
            {
                $deleteURL = $this->createLink('repohook', 'delete', "id=$id&confirm=yes");
                echo html::a("javascript:ajaxDelete(\"$deleteURL\",\"repohookList\",confirmDelete)", '<i class="icon-trash"></i>', '', "title='{$lang->repohook->delete}' class='btn'");
            }
            ?>
          </td>
        </tr>
        <?php endforeach;?>
      </tbody>
    </table>
    <?php if($hooks):?>
    <div class='table-footer'><?php $pager->show('right', 'pagerjs');?></div>
    <?php endif;?>
  </form>
</div>
<?php include '../../common/view/footer.html.php';?>

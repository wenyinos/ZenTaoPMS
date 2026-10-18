<?php
/**
 * The create view file of repohook module of ZenTaoPMS (WenYinOS community build).
 *
 * @package repohook
 */
?>
<?php include '../../common/view/header.html.php';?>
<div id='mainContent' class='main-content'>
  <div class='center-block'>
    <div class='main-header'>
      <h2><?php echo $lang->repohook->create;?></h2>
    </div>
    <form id='repohookForm' method='post' class='ajaxForm'>
      <table class='table table-form'>
        <tr>
          <th><?php echo $lang->repohook->provider;?></th>
          <td><?php echo html::select('provider', $providers, 'github', "class='form-control'");?></td>
          <td></td>
        </tr>
        <tr>
          <th><?php echo $lang->repohook->name;?></th>
          <td><?php echo html::input('name', '', "class='form-control'");?></td>
          <td></td>
        </tr>
        <tr>
          <th><?php echo $lang->repohook->secret;?></th>
          <td><?php echo html::input('secret', '', "class='form-control' autocomplete='off'");?></td>
          <td><?php echo $lang->repohook->note->secret;?></td>
        </tr>
        <tr>
          <th><?php echo $lang->repohook->product;?></th>
          <td><?php echo html::select('product', $products, 0, "class='form-control chosen'");?></td>
          <td></td>
        </tr>
        <tr>
          <th><?php echo $lang->repohook->status;?></th>
          <td><?php echo html::select('status', $lang->repohook->statusList, 'normal', "class='form-control'");?></td>
          <td></td>
        </tr>
        <tr>
          <th></th>
          <td colspan='2' class='text-muted'><?php echo $lang->repohook->note->payload;?><br/><code><?php echo $baseURL;?>&lt;令牌保存后生成&gt;</code></td>
        </tr>
        <tr>
          <th></th>
          <td colspan='2' class='text-center form-actions'>
            <?php echo html::submitButton('', '', 'btn btn-wide btn-primary');?>
            <?php echo html::backButton('', '', 'btn btn-wide');?>
          </td>
        </tr>
      </table>
    </form>
  </div>
</div>
<?php include '../../common/view/footer.html.php';?>

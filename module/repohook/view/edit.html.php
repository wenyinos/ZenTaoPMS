<?php
/**
 * The edit view file of repohook module of ZenTaoPMS (WenYinOS community build).
 *
 * @package repohook
 */
?>
<?php include '../../common/view/header.html.php';?>
<div id='mainContent' class='main-content'>
  <div class='center-block'>
    <div class='main-header'>
      <h2><?php echo $lang->repohook->edit;?></h2>
    </div>
    <form id='repohookForm' method='post' class='ajaxForm'>
      <table class='table table-form'>
        <tr>
          <th><?php echo $lang->repohook->provider;?></th>
          <td><?php echo html::select('provider', $providers, $hook->provider, "class='form-control'");?></td>
          <td></td>
        </tr>
        <tr>
          <th><?php echo $lang->repohook->name;?></th>
          <td><?php echo html::input('name', $hook->name, "class='form-control'");?></td>
          <td></td>
        </tr>
        <tr>
          <th><?php echo $lang->repohook->secret;?></th>
          <td><?php echo html::input('secret', $hook->secret, "class='form-control' autocomplete='off'");?></td>
          <td><?php echo $lang->repohook->note->secret;?></td>
        </tr>
        <tr>
          <th><?php echo $lang->repohook->product;?></th>
          <td><?php echo html::select('product', $products, $hook->product, "class='form-control chosen'");?></td>
          <td></td>
        </tr>
        <tr>
          <th><?php echo $lang->repohook->status;?></th>
          <td><?php echo html::select('status', $lang->repohook->statusList, $hook->status, "class='form-control'");?></td>
          <td></td>
        </tr>
        <tr>
          <th><?php echo $lang->repohook->token;?></th>
          <td colspan='2'><code><?php echo $baseURL . $hook->token;?></code></td>
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

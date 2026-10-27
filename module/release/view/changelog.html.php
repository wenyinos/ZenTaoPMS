<?php
/**
 * The changelog view file of release module of ZenTaoPMS (WenYinOS community build).
 *
 * 按需求 / 已修复 Bug / 遗留问题分组渲染发布说明，输出可直接复制使用的 markdown。
 *
 * @package release
 */
?>
<?php include '../../common/view/header.html.php';?>
<div id='mainContent' class='main-content'>
  <div class='main-header'>
    <h2><?php echo $lang->release->changelog . $lang->colon . $release->name;?></h2>
    <div class='pull-right'>
      <button type='button' class='btn btn-primary' id='copyChangelog'><i class='icon icon-copy'></i> <?php echo $lang->release->copyChangelog;?></button>
      <?php echo html::a(inlink('view', "releaseID={$release->id}"), $lang->goback, '', "class='btn'");?>
    </div>
  </div>
  <div class='cell'>
    <textarea id='changelogText' class='form-control' rows='26' style='font-family:monospace;white-space:pre;'><?php echo htmlspecialchars($markdown, ENT_QUOTES, 'UTF-8');?></textarea>
    <p class='text-muted'><?php echo $lang->release->changelogTip;?></p>
  </div>
</div>
<script>
$(function()
{
    $('#copyChangelog').click(function()
    {
        var textarea = document.getElementById('changelogText');
        textarea.select();
        textarea.setSelectionRange(0, textarea.value.length);

        var done = function(){ $('#copyChangelog').text('<?php echo $lang->release->copied;?>'); };
        if(navigator.clipboard && navigator.clipboard.writeText)
        {
            navigator.clipboard.writeText(textarea.value).then(done, function(){ document.execCommand('copy'); done(); });
        }
        else
        {
            document.execCommand('copy');
            done();
        }
    });
});
</script>
<?php include '../../common/view/footer.html.php';?>

<h3>当日の時間、コメント</h3>
<form action="<?php echo url_for('kintai/comment') ?>" method="post">
<table>
<?php echo $form ?>
<tr>
<td colspan="2"><input type="submit" value="<?php echo __('登録') ?>" /></td>
</tr>
</table>
</form>


<script type="text/javascript"> 
$(function(){
    $('#view1').csv2table('/kintai/getcsv');
});
</script> 
<div id="view1"></div>

<html>
	<form method="post" action="?action=onRun">
	<div class="category">
		<div>
		<select name = "firstCat">
		<?php foreach($firstCats as $key=>$value):?>
		<option  value="<?php echo $value["title"];?>" <?php if($value["title"] == $status['firstCat']): ?> selected="selected" <?php endif; ?> ><?php echo $value["title"]; ?></option>
		<?php endforeach;?>
		</select>
		
		<select name = "secondCat">
		<?php foreach($secondCats as $key=>$value):?>
		<option value="<?php echo $value["title"];?>"  <?php if($value["title"] == $status['secondCat']): ?> selected="selected" <?php endif; ?> ><?php echo $value["title"];?> </option>
		<?php endforeach;?>
		</select>
		</div>
 	</div>
	<div> 
		<input type="submit" value="开始采集">
		<hr>
		<p>上次采集至第<?php echo intval($status["listIndex"]) ;?>页；</p>
	</div>
	</form>
</html>
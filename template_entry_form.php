<div id="takeover">
	<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" enctype="multipart/form-data">
		<input type="hidden" name="rendered_citations" value="<?php echo strip_tags(stripslashes($_POST['rendered_citations'])); ?>" />

		<div style="width: 30%; float: left;">
			<input type="text" id="name" name="post_topic" size="60" style="width: 80%; margin: 5px;font-size:20px;cursor:pointer;" value="<?php echo strip_tags(stripslashes($_POST['post_topic'])); ?>" /><br>
			<select name="post_category_id" style="margin: 5px; margin-left: 15px;"><?php echo $category_options; ?></select>
		</div>

		<div style="float: left; width: 60%;">
			<textarea id="description" name="post_introduction" rows="3" cols="20" style="width: 80%;font-size:12px;color:grey;"><?php echo strip_tags(stripslashes($_POST['post_introduction'])); ?></textarea>
		</div>

		<div style="clear: both; width: 30%; float: left; margin-top: 30px; overflow:auto;">    
			<strong>Citations</strong><span id="detected_book">Book</span><br /><span id="tooltip">Type your citation and hit <b>enter</b>...</span><br />
			<textarea name="post_citations" id="citations" rows="16" cols="30"><?php echo strip_tags(stripslashes($_POST['post_citations'])); ?></textarea>
			<input style="clear: both;" type="submit" name="submit" value="Save / Share" class="btn btn-primary" />
		</div>

		<div style="width: 70%; float: right; margin-top: 30px;">    
			<strong>Preview</strong><br />
			<div id="sortable" style="height: 500px;overflow: auto; border: 1px solid #eee;"></div>		
		</div>

	</form>
</div>

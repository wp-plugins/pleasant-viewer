<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" enctype="multipart/form-data">

<div>
<strong>Topic</strong> (Optional)<br />
<input type="text" name="post_topic" size="60" value="<?php echo strip_tags(stripslashes($_POST['post_topic'])); ?>" />
</div>

<div>
<strong>Introduction / Description</strong> (Optional)<br />
<textarea name="post_introduction" rows="3" cols="80"><?php echo strip_tags(stripslashes($_POST['post_introduction'])); ?></textarea>
</div>

<div>
<strong>Category:</strong><br />
<select name="post_category_id"><?php echo $category_options; ?></select>
</div>

<div>
Put each citation on its own line.<br />
Currently supported books: KJV Bible passages (eg Gen 1:1) and <em>Science &amp; Health</em> references (eg 1:1)
</div>

<div>

<div style="width: 30%; float: left;">
<strong>Citations</strong><span style="color: #f00;">*</span><br />
<textarea name="post_citations" rows="16" cols="20"><?php echo strip_tags(stripslashes($_POST['post_citations'])); ?></textarea>
</div>

<div style="width: 55%; float: left;">
<strong>Preview</strong>
<div style="border: 1px solid #ddd; width: 500px; height: 600px;"></div>
</div>
<div>

<div style="clear: both;"><input type="submit" name="submit" value="Submit" /></div>

</form>


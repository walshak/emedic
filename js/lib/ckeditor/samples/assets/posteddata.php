<!DOCTYPE html>
<?php

?>
<html>
<head>
	<meta charset="utf-8">
	<title>Sample &mdash; CKEditor</title>
	<link rel="stylesheet" href="sample.css">
</head>
<body>
	<h1 class="samples">
		CKEditor &mdash; Posted Data
	</h1>
	<table border="1" cellspacing="0" id="outputSample">
		<colgroup><col width="120"></colgroup>
		<thead>
			<tr>
				<th>Field&nbsp;Name</th>
				<th>Value</th>
			</tr>
		</thead>
<?php

if (!empty($_POST))
{
	foreach ( $_POST as $Vzzgsb4i5jlb => $Vpux5qda0xly )
	{
		if ( ( !is_string($Vpux5qda0xly) && !is_numeric($Vpux5qda0xly) ) || !is_string($Vzzgsb4i5jlb) )
			continue;

		if ( get_magic_quotes_gpc() )
			$Vpux5qda0xly = htmlspecialchars( stripslashes((string)$Vpux5qda0xly) );
		else
			$Vpux5qda0xly = htmlspecialchars( (string)$Vpux5qda0xly );
?>
		<tr>
			<th style="vertical-align: top"><?php echo htmlspecialchars( (string)$Vzzgsb4i5jlb ); ?></th>
			<td><pre class="samples"><?php echo $Vpux5qda0xly; ?></pre></td>
		</tr>
	<?php
	}
}
?>
	</table>
	<div id="footer">
		<hr>
		<p>
			CKEditor - The text editor for the Internet - <a class="samples" href="http://ckeditor.com/">http:
		</p>
		<p id="copy">
			Copyright &copy; 2003-2013, <a class="samples" href="http://cksource.com/">CKSource</a> - Frederico Knabben. All rights reserved.
		</p>
	</div>
</body>
</html>

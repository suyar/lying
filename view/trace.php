<!DOCTYPE html>
<html lang="zh-cn">
<head>
	<meta charset="UTF-8">
	<title>Error</title>
	<style>
		* { margin: 0;padding: 0; }
		th,td { font-weight: normal;text-align: left;padding: 5px 10px; }
		td { background-color: #FFFFCC; }
	</style>
</head>
<body style="font-family: 'Microsoft Yahei';padding: 0 30px;">
	<div style="background-color: #F3F3F3;padding: 0 15px 15px 15px;border-radius: 5px;margin: 20px 0;">
		<p style="color: red;font-size: 20px;font-weight: bold;padding: 15px 0;">Some errors occurred while program running:</p>
		<p><?= $msg ?></p>
	</div>
	<div style="background-color: #F3F3F3;padding: 0 15px 15px 15px;border-radius: 5px;margin: 10px 0;">
		<table style="border-collapse: collapse;border: 1px solid #000;width: 100%;border-color: #999;" border="1">
			<caption style="text-align: left;font-size: 20px;font-weight: bold;padding: 15px 0;">Trace</caption>
			<tr>
				<th>No.</th>
				<th>File</th>
				<th>Line</th>
				<th>Code</th>
			</tr>
			<?php foreach ($trace as $k=>$t): ?>
			<tr>
				<td><?= $k + 1 ?></td>
				<td><?= $t['file'] ?></td>
				<td><?= $t['line'] ?></td>
				<td><?= $t['code'] ?></td>
			</tr>
			<?php endforeach; ?>
		</table>
	</div>
</body>
</html>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title><?= $info ;?></title>
    <style>
        body { font-family: "Consolas"; }
        .trace { width: 100%;border: 1px solid #000; }
        .trace h1 { margin: 0;padding: 5px 10px;font-size: 14px;border-bottom: 1px solid #000; }
        .trace ul { list-style: none;padding: 5px 10px;margin: 0;font-size: 13px;background-color: #000;color: #fff; }
    </style>
</head>
<body>
<div class="trace">
    <h1>An error occurred while Lying running.</h1>
    <ul>
        <li>Lying Framework [Version <?= Lying::version(); ?>]. Copyright (c) 2017 Lying. All rights reserved.</li>
        <li>Copyright (c) 2018 Lying. All rights reserved.</li>
        <li>[Error Code] ：<?= $code; ?></li>
        <li>[Error Info] ：<?= $info; ?></li>
        <li>[Error File] ：<?= $file; ?></li>
        <li>[Error Line] ：<?= $line; ?></li>
        <li>&nbsp;</li>
        <?php foreach ($trace as $t): ?>
            <li><?= var_export($t, true); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
</body>
</html>

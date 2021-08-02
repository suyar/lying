<?php
$fp = fopen('log', 'a');
flock($fp, LOCK_EX);

rename('log', 'log1');

fwrite($fp, 123);

flock($fp, LOCK_UN);
fclose($fp);
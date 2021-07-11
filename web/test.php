<?php






$dsn = 'mysql:host=;dbname=lying;charset=utf8';
$user = 'root';
$pass = '';

$pdo = new \PDO($dsn, $user, $pass);
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
$pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

$statement = $pdo->prepare('select id,id from user');
$statement->execute();

var_dump($statement->fetchAll());

//echo $pdo->lastInsertId('username');
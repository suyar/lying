<?php
ini_set('display_errors',1);


echo strtolower(preg_replace('/((?<=[a-z])(?=[A-Z]))/', '_', 'user'));
die;


$dsn = 'mysql:host=127.0.0.1;dbname=lying;charset=utf8';
$user = 'root';
$pass = '';

$pdo = new \PDO($dsn, $user, $pass);
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
$pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

$statement = $pdo->prepare("SELECT * FROM `user` WHERE MATCH (`username`) AGAINST (?)");
$statement->bindValue(1, "'sususu' IN BOOLEAN MODE");

$statement->execute();

var_dump($statement->fetchAll());

//echo $pdo->lastInsertId('username');
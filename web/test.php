<?php






$dsn = 'mysql:host=;dbname=lying;charset=utf8';
$user = 'root';
$pass = '#';

$pdo = new \PDO($dsn, $user, $pass);
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);

$statement = $pdo->prepare('select * from user where id=:id or username=:id1');
$statement->bindValue(':id', 1);
$statement->bindValue(':id1', 1);
$statement->execute();

var_dump($statement->fetchAll());

//echo $pdo->lastInsertId('username');
<?php






$dsn = 'mysql:host=121.196.204.163;dbname=lying;charset=utf8';
$user = 'root';
$pass = 'Suyaqi1992#';

$pdo = new \PDO($dsn, $user, $pass);

$statement = $pdo->prepare('insert into user (id,username) values (10,"testname1")');
$statement->execute();

echo $pdo->lastInsertId('username');
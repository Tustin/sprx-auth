<?php
//This script will setup your database, tables and columns.
include('autoload.php');

if (empty($db_user) || empty($db_password))
{
  die("Missing MySQL user credentials. Please specify a username and password inside 'autoload.php' before running the setup.");
}
try {
$setup = new PDO("mysql:host=".$db_host, $db_user, $db_password);
} 
catch (PDOException $e) {
    die("An error occurred when logging into MySQL - " . $e->getMessage());
}
echo '<pre>';

$query = $setup->prepare("CREATE DATABASE " . DATABASE_NAME);

if ($query->execute()){
echo "Created database '" . DATABASE_NAME ."' <font color='green'>successfully</font><br>";
} else {
  echo "<font color='red'>ERROR</font> occurred when creating database '" . DATABASE_NAME . "' - " . $query->errorCode() . " (maybe MySQL user doesn't have sufficent permissions?)<br>";
}

$query = $setup->prepare("CREATE TABLE " . DATABASE_NAME . ".users (
  `license` text NOT NULL,
  `ip` text NOT NULL,
  `dateline` bigint(20) NOT NULL,
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `times_banned` int(11) NOT NULL,
  `banned` tinyint(4) NOT NULL,
  `ban_reason` text NOT NULL,
  `ban_dateline` int(11) NOT NULL,
  `unban_dateline` int(11) NOT NULL
)");

if ($query->execute()){
  echo "Created table 'users' in database '" . DATABASE_NAME . "' <font color='green'>successfully</font><br>";
} else {
  echo "<font color='red'>ERROR</font> occurred when creating table 'users' - " . $query->errorCode() . "<br>";
}

$query = $setup->prepare("CREATE TABLE " . DATABASE_NAME . ".log (
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `license` text NOT NULL,
  `ip` text NOT NULL,
  `dateline` bigint(20) NOT NULL
)");

if ($query->execute()){
  echo "Created table 'log' in database '" . DATABASE_NAME . "' <font color='green'>successfully</font><br>";
} else {
  echo "<font color='red'>ERROR</font> occurred when creating table 'log' - " . $query->errorCode() . "<br>";
}

echo "Completed operations. <br>";

echo '</pre>';

<?php
include("config.php");

$server = DBHOST;
$user = DBUSER;
$password = DBPASSWORD;
$db = DBDATABASE;

$conn = mysqli_connect($server, $user, $password, $db);
if (!$conn) {
    echo "Cannot connect to database server";
    exit;
}
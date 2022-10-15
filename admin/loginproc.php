<?php

session_start();

$user=$_POST['username'];
$pass=$_POST['password'];

if($user=='mary' && $pass=='secretpass') {
	$_SESSION['user']='mary';
	header('location:index.php');
} else {
	header('location:login.php?e=1');
}
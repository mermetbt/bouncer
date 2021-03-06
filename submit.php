<?php
require('inc/common.php');
require('inc/mailer.php');


function mail_and_die($m)
{
  mailer('it@xinchejian.com', 'Error in '.__FILE__, $m);
  die($m);
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	header('HTTP/1.1 303 See Other');
	header('Location: /submit.html');
	die('must be POST');
}


$amount = $_POST['amount'];
$email = trim($_POST['email']);
if (!filter_var($email, FILTER_VALIDATE_EMAIL))
	mail_and_die('invalid email');
if ($amount == '100')
	$months = 1;
else if ($amount == '450')
	$months = 6;
else if ($amount == '900')
	$months = 12;
else if ($amount == '5000')
	$months = 12;
else
	mail_and_die('wrong amount');

// TODO: generate this salt
$salt = 'salT';
// Friendlier pincode instead of password
$crc = crc32($salt.strtoupper($email)) & 0x7FFFFFFF;//remove sign
$password = sprintf("%06u", $crc % 1000000);

// add SetEnv MYSQL_PASSWORD "blah" to this site's Apache conf
$link = mysql_connect('localhost', 'webuser', getenv('MYSQL_PASSWORD'))
	or mail_and_die('mysql_connect error');
$email2 = '"'.mysql_real_escape_string($email, $link).'"';
$amount2 = '"'.mysql_real_escape_string($amount, $link).'"';
$password2 = '"'.mysql_real_escape_string($password, $link).'"';
$salt2 = '"'.mysql_real_escape_string($salt, $link).'"';

mysql_query("INSERT IGNORE members.Users (email,since) VALUES($email2,NOW())", $link)
	or mail_and_die('mysql_query INSERT Users error');
$isnew = mysql_affected_rows($link) == 1;

mysql_query("INSERT members.Payments (email, submitted, amount) VALUES($email2, NOW(), $amount2)", $link)
	or mail_and_die('mysql_query INSERT Payments error');

// Give new members the benefit of the doubt (trust, but verify):
// FIXME: might fail because of unique password (change salt)
mysql_query("UPDATE members.Users SET paid = IF(CURDATE()<paid,paid,CURDATE()) + INTERVAL $months MONTH, salt = $salt2, password = $password2 WHERE email = $email2", $link)
	or mail_and_die('mysql_query UPDATE error');
if (mysql_affected_rows($link) != 1)
	mail_and_die('mysql_affected_rows != 1');

mysql_close($link);
unset($link);

$subject = 'Welcome to Xinchejian 欢迎加入新车间';
$body = "Welcome! 欢迎！

You can now open the door by going to http://bouncer/
PIN: $password

Note that your access will be revoked if no payment was made.

-- the script that sends out these emails";
mailer($email, $subject, $body);

if ($isnew)
	$neworold = "New";
else
	$neworold = "Old";
mailer('finances@xinchejian.com', "$neworold member: $email, paid $amount for $months month(s).", '-- '.__FILE__);

header('HTTP/1.1 303 See Other');
header('Location: /welcome.html');


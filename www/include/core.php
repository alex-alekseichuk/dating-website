<?php

// basic

$PHPVersion = explode(".",  phpversion());
if (($PHPVersion[0] < 4) || ($PHPVersion[0] == 4  && $PHPVersion[1] < 1)) {
    echo "Sorry. This program requires PHP 4.1 and above to run.<br>You may upgrade your php at <a href='http://www.php.net/downloads.php'>http://www.php.net/downloads.php</a>";
    exit;
}
session_start();
header('Pragma: ');
header('Cache-control: ');
header('Expires: ');


function to_html($Value)
{
	return nl2br(htmlspecialchars($Value));
}
function to_url($Value)
{
	return urlencode($Value);
}
function get_session($parameter_name)
{
    return isset($_SESSION[$parameter_name]) ? $_SESSION[$parameter_name] : "";
}
function set_session($param_name, $param_value)
{
    $_SESSION[$param_name] = $param_value;
}
function get_cookie($parameter_name)
{
    return isset($_COOKIE[$parameter_name]) ? $_COOKIE[$parameter_name] : "";
}
function set_cookie($parameter_name, $param_value, $expired = -1)
{
  if ($expired == -1)
    $expired = time() + 3600 * 24 * 366;
  elseif ($expired && $expired < time())
    $expired = time() + $expired;
  setcookie ($parameter_name, $param_value, $expired);  
}

function strip($value)
{
  if(get_magic_quotes_gpc() != 0)
  {
    if(is_array($value))  
      foreach($value as $key=>$val)
        $value[$key] = stripslashes($val);
    else
      $value = stripslashes($value);
  }
  return $value;
}
function get_param($parameter_name, $default_value = "")
{
    $parameter_value = "";
    if(isset($_POST[$parameter_name]))
        $parameter_value = strip($_POST[$parameter_name]);
    else if(isset($_GET[$parameter_name]))
        $parameter_value = strip($_GET[$parameter_name]);
    else
        $parameter_value = $default_value;
    return $parameter_value;
}

function get_param_array($parameter_name)
{
	$arr = Array();
	
    if(isset($_POST[$parameter_name]))
	{
		if (is_array($_POST[$parameter_name]))
		{
			$arr = $_POST[$parameter_name];
		}
		else
		{
			$arr = Array($_POST[$parameter_name]);
		}
	}
    else if(isset($_GET[$parameter_name]))
	{
		if (is_array($_GET[$parameter_name]))
		{
			$arr = $_GET[$parameter_name];
		}
		else
		{
			$arr = Array($_GET[$parameter_name]);
		}
	}
    return $arr;
}


function get_checks_param($name)
{
	$arr = get_param_array($name);
	$v = 0;
	foreach ($arr as $param)
	{
		$v |= (1 << ($param - 1));
	}
	return $v;
}



function add_error($s, $sError)
{
	if ($s != "")
		$s .= "<BR>";
	return $s . $sError;
}


function send_email($to, $from, $subject, $message, $type)
{
	$headers = "";
	$headers .= "From: " . $from . "\n";
	if ($type == "")
		$type = "text/plain";
	$headers .= "Content-type: " . $type . "\n";
	mail($to, $subject, $message, $headers);
}

function debug($s)
{
	echo "<hr>" . $s . "<hr>\n";
}


?>
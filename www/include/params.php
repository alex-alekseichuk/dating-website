<?php

// to collect parameters
// to correct going from one block(page/form) to another one


//include_once("core.php");


// hash to save parameters values
// it should be filled by get_block_params($name) call
$g_param_values = Array();




// just get current parameters set
function get_params()
{
	global $g_param_values;
	$sRet = "";
	foreach ($g_param_values as $key => $val)
	{
		if ($sRet != "")
			$sRet .= "&";
		$sRet .= $key . "=" . $val;
	}
	return $sRet;
}	

// get current params but change/set one specified parameter
function correct_param($name, $new_value)
{
	global $g_param_values;
	$sRet = "";
	foreach ($g_param_values as $key => $val)
	{
		if ($sRet != "")
			$sRet .= "&";
		$sRet .= $key . "=" . ($key==$name ? $new_value : $val);
	}
	if (! isset($g_param_values[$name]))
	{
		if ($sRet != "")
			$sRet .= "&";
		$sRet .= $name . "=" . $new_value;
	}
	return $sRet;
}	

// get current params but change/set one specified parameter
function correct_param2($name, $new_value, $name2, $new_value2)
{
	global $g_param_values;
	$sRet = "";
	foreach ($g_param_values as $key => $val)
	{
		if ($sRet != "")
			$sRet .= "&";
		$sRet .= $key . "=" . ($key==$name ? $new_value : ($key==$name2 ? $new_value2 : $val));
	}
	if (! isset($g_param_values[$name]))
	{
		if ($sRet != "")
			$sRet .= "&";
		$sRet .= $name . "=" . $new_value;
	}
	if (! isset($g_param_values[$name2]))
	{
		if ($sRet != "")
			$sRet .= "&";
		$sRet .= $name2 . "=" . $new_value2;
	}
	return $sRet;
}	

// get current params but change/set several specified parameter
// $new_params is a hash of new params.
function correct_params($new_params)
{
	global $g_param_values;
	$sRet = "";
	foreach ($g_param_values as $key => $val)
	{
		if ($sRet != "")
			$sRet .= "&";
		$sRet .= $key . "=";
		if (isset($new_params[$key]))
		{
			$sRet .= $new_params[$key];
			$new_params[$key] = "";
		}
		else
			$sRet .= $val;
	}
	foreach ($new_params as $key => $val)
	{
		if ($val != "")
		{
			if ($sRet != "")
				$sRet .= "&";
			$sRet .= $key . "=" . $val;
		}
	}
	return $sRet;
}	



// we need to call this method one time on the page
// to fill g_param_values hash
// $name is a root (page) block name
function get_block_params($name)
{
	global $g_param_values;
	global $g_params;
	global $g_depends;
	if (isset($g_params[$name]))
	{
		foreach ($g_params[$name] as $key)
		{
			if (! isset($g_param_values[$key]))
			{
				$g_param_values[$key] = get_param($key);
			}
		}
	}
	if (isset($g_depends[$name]))
	{
		foreach ($g_depends[$name] as $key)
		{
			get_block_params($key);
		}
	}
}	
	
function debug_params()
{
	global $g_param_values;
	foreach ($g_param_values as $key => $val)
	{
		echo $key . "=" . $val . "<br>";
	}
	echo "<hr>";
}

?>
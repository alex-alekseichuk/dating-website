<?php

//include_once("block.php");
//include_once("db.php");



$g_months = Array(
	"1" => "Январь",
	"2" => "Февраль",
	"3" => "Март",
	"4" => "Апрель",
	"5" => "Май",
	"6" => "Июнь",
	"7" => "Июль",
	"8" => "Август",
	"9" => "Сентябрь",
	"10" => "Октябрь",
	"11" => "Ноябрь",
	"12" => "Декабрь"
);



// global function useful for record forms


// returns the title of the field
function rec_field_title($field, $name)
{
	if (isset($field["title"]))
	{
		return $field["title"];
	} else {
		return $name;
	}
}



// returns the list of pairs like name=value
// separated by ','
// ready to insert into sql-update
function rec_fields_to_sql(&$fields)
{
	$ret = "";
	foreach ($fields as $name => $i)
	{
		if (! isset($i["noupdate"]))
		{
			if ($ret != "") $ret .= ",";
			$t = "";

			if (isset($i["type"]) && (
				$i["type"] == "checks" || 
				$i["type"] == "checksOn" || 
				$i["type"] == "int" || 
				$i["type"] == "float" ||
				$i["type"] == "ilov" ||
				$i["type"] == "iselect"
				))
				$t = "Number" ;
			if (isset($i["type"]) && $i["type"] == "plain") $t = "Plain";
			if (isset($i["type"]) && $i["type"] == "check") $t = "Check";

			if (isset($i["type"]) && $i["type"] == "plain")
				$v = $i["plain"];
			else
				$v = $i["value"];

			$ret .= $name . "=" . to_sql($v, $t);
		}
	}
	return $ret;
}


// returns the list of fields names
// separated by ','
// ready to insert into sql-insert
function rec_sql_fields(&$fields)
{
	$ret = "";
	foreach ($fields as $name => $i)
	{
		if (! isset($i["noinsert"]))
		{
			if ($ret != "") $ret .= ",";
			$ret .= $name;
		}
	}
	return $ret;
}

// returns the list of fields values
// separated by ','
// ready to insert into sql-insert
function rec_sql_values(&$fields)
{
	$ret = "";
	foreach ($fields as $i)
	{
		if (! isset($i["noinsert"]))
		{
			if ($ret != "") $ret .= ",";
			$t = "";

			if (isset($i["type"]) && (
				$i["type"] == "int" || 
				$i["type"] == "float" ||
				$i["type"] == "ilov" ||
				$i["type"] == "iselect"
				))
				$t = "Number" ;
			if (isset($i["type"]) && $i["type"] == "plain") $t = "Plain";
			if (isset($i["type"]) && $i["type"] == "check") $t = "Check";

			if (isset($i["type"]) && $i["type"] == "plain")
				$v = $i["plain"];
			else
				$v = $i["value"];

			$ret .= to_sql($v, $t);
		}
	}
	return $ret;
}


// get values of fields from http
// returns error strings or ""
function rec_get_http($db, &$fields, $_id, $form, $_table)
{
	global $HTTP_POST_FILES;
	$sRet = "";
	foreach ($fields as $name => $i)
	{
		if (isset($i["type"]) && $i["type"] == "file")
		{
			if (isset($HTTP_POST_FILES[$name]) && is_uploaded_file($HTTP_POST_FILES[$name]["tmp_name"]))
			{
/*
				if (isset($i["exts"]))
				{
					$sExts = "";
					foreach ($i["exts"] as $ext)
					{
						if ($sExts != "") $sExts .= "|";
						$sExts .= "(\." . $ext . ")";
					}
					if (preg_match("/((\.gif)|(\.jpg)|(\.jpeg)|(\.png))$/i", 
						$HTTP_POST_FILES["photo"]['name'],
						$sExt) == 1)
					{
						
					}
				}
*/

				// ! ! !

				$sFile = $i["file"];
				$sFile = ereg_replace("%rand%", md5(uniqid(rand())), $sFile);
				if ($i["value"] != "")
					unlink($i["dir"] . $i["value"]);
// ! ! ! recomment here
				if (1 == 0)
//				if (! move_uploaded_file($HTTP_POST_FILES[$name]['tmp_name'],
//					$i["dir"] . $sFile))
				{
					$sRet .= "Невозможно сохранить файл " . rec_field_title($i, $name) . "<br>";
					$fields[$name]["value"] = "";
				} else {
					$fields[$name]["value"] = $sFile;
				}
			} else {
				if ((! isset($i["nocheck"])) && (! isset($i["optional"])))
				{
					if ($i["value"] == "")
						$sRet .= "Поле " . rec_field_title($i, $name) . " обязательное<br>";
				}
			}
		}
		else if (! isset($i["nohttp"]))
		{
			//if ((! isset($i["type"])) || $i["type"] != "date3")
			$v = get_param($name, $i["value"]);

			if (! isset($i["nocheck"]))
			{
				if (isset($i["type"]) && ($i["type"] == "int" || $i["type"] == "float"))
				{
					if ($v == "")
					{
						if (! isset($i["optional"]))
							$sRet .= "Поле " . rec_field_title($i, $name) . " обязательное<br>";
					} else {
						if ($i["type"] == "int") $v = (int)$v;
						if ($i["type"] == "float") $v = (double)$v;
						if (isset($i["min"]) && (0 + $v) < $i["min"])
						{
							$sRet .= "Поле " . rec_field_title($i, $name) . " не должно быть меньше " . $i["min"] . "<br>";
						}
						if (isset($i["max"]) && (0 + $v) > $i["max"])
						{
							$sRet .= "Поле " . rec_field_title($i, $name) . " не должно быть больше " . $i["max"] . "<br>";
						}
						if (isset($i["unique"]))
						{
							if ($db->DLookUp("SELECT count(*) FROM $_table WHERE " . $form . "Id<>" . to_sql($_id, "Number") . " AND " . $name . "=" . to_sql($v, "Number")) > 0)
							{
								$sRet .= "Поле " . rec_field_title($i, $name) . " должно быть уникальным<br>";
							}
						}


					}
				}
				else if (isset($i["type"]) && ($i["type"] == "iselect" || $i["type"] == "sselect"))
				{
					if ($v == "")
					{
						if (! isset($i["optional"]))
							$sRet .= "Поле " . rec_field_title($i, $name) . " обязательное<br>";
					} else {
						if (isset($i["sqlcheck"]))
						{
							if ($db->DLookUp($i["sqlcheck"] . to_sql($v, ($i["type"] == "iselect" ? "Number" : ""))) == 0)
								$sRet .= "Поле " . rec_field_title($i, $name) . " задано некорректно<br>";
						}
					}
				}
				else if (isset($i["type"]) && ($i["type"] == "ilov" || $i["type"] == "slov"))
				{
					if ($v == "")
					{
						if (! isset($i["optional"]))
							$sRet .= "Поле " . rec_field_title($i, $name) . " обязательное<br>";
					} else {
						if (isset($i["options"]))
						{
							if (! isset($i["options"][$v]))
								$sRet .= "Поле " . rec_field_title($i, $name) . " задано некорректно<br>";
						}
					}
				}
				else if (isset($i["type"]) && $i["type"] == "check")
				{
					$v = get_param($name, "0");
					if ($v == "")
					{
						if (! isset($i["optional"]))
							$sRet .= "Поле " . rec_field_title($i, $name) . " обязательное<br>";
					} else {
						if ($i["value"] != 0 && $i["value"] != 1)
							$sRet .= "Поле " . rec_field_title($i, $name) . " задано некорректно<br>";
					}
				}
				else if (isset($i["type"]) && ($i["type"] == "checks" || $i["type"] == "checksOn"))
				{
					$v = get_checks_param($name);
					if ($v == 0)
					{
						if (! isset($i["optional"]))
							$sRet .= "Поле " . rec_field_title($i, $name) . " обязательное<br>";
					}
				}
				else if (isset($i["type"]) && $i["type"] == "date3")
				{
					$vY = get_param($name . "Year", "");
					$vM = get_param($name . "Month", "");
					$vD = get_param($name . "Day", "");
					if ($vD == "" || $vM == "" || $vY == "")
					{
						if (! isset($i["optional"]))
							$sRet .= "Поле " . rec_field_title($i, $name) . " обязательное<br>";
					} else {
						if ($vY < 1900 || $vY > 2005 || $vM < 1 || $vM > 12 || $vD < 1 || $vD > 31)
							$sRet .= "Поле " . rec_field_title($i, $name) . " задано некорректно<br>";
						else
							$v = $vY . "-" . $vM . "-" . $vD;
					}
				} else { // pure text field
					if ($v == "")
					{
						if (! isset($i["optional"]))
							$sRet .= "Поле " . rec_field_title($i, $name) . " обязательное<br>";
					} else {
						if (isset($i["min"]) && strlen($v) < $i["min"])
						{
							$sRet .= "Поле " . rec_field_title($i, $name) . " должно быть не менее " . $i["min"] . "символов<br>";
						}
						if (isset($i["max"]) && strlen($v) > $i["max"])
						{
							$sRet .= "Поле " . rec_field_title($i, $name) . " должно быть не более " . $i["min"] . "символов<br>";
						}
					}
				}
			}

			$fields[$name]["value"] = $v;
		}
	}
	return $sRet;
}


// get values from db
// $sql is a sub-sql like "FROM table WHERE ..."
function rec_get_db($db, &$fields, $sqlFromWhere)
{
	$fs = "";
	foreach ($fields as $name => $i)
	{
		if (! isset($i["nodb"]))
		{
			if ($fs != "") $fs .= "," ;
			if (isset($i["dbSelect"]))
				$fs .= $i["dbSelect"];
			else	
				$fs .= $name;
		}
	}

	$sql = "SELECT " . $fs . " " . $sqlFromWhere;
//echo "<hr>$sql<hr>";

	$ret = 0;		
	$db->query($sql);
	if ($row = $db->fetch_row())
	{
		foreach ($fields as $name => $i)
		{
			if ((! isset($i["nodb"])) && isset($row[$name]))
			{
				if (isset($i["type"]) && $i["type"] == "check")
				{
					$fields[$name]["value"] = ($row[$name] == 'Y' ? 1 : ($row[$name] == 'N' ? 0 : ""));
				} else {
					$fields[$name]["value"] = $row[$name];
//echo "<hr>$name : " . $fields[$name]["value"] . "<hr>";
				}
			}
		}
		$ret = 1;
	}
	$db->free_result();
	return $ret;
}

function rec_parse_checks($name, $db, &$html, $sql, $mask)
{
	if ($db->query($sql))
	{
		while ($row = $db->fetch_row())
		{
			$html->setvar("value", $row[0]);
			$html->setvar("title", $row[1]);
			if ($mask & (1 << ($row[0] - 1)))
				$html->setvar("checked", " checked");
			else
				$html->setvar("checked", "");
			$html->parse($name, true);
		}
		$db->free_result();
	}
}
function rec_parse_checksOn($name, $db, &$html, $sql, $mask)
{
	if ($db->query($sql))
	{
		while ($row = $db->fetch_row())
		{
			$html->setvar("value", $row[0]);
			$html->setvar("title", $row[1]);
			if ($mask & (1 << ($row[0] - 1)))
				$html->parse($name, true);
		}
		$db->free_result();
	}
}

// parse all fields
function rec_parse_values($db, &$html, &$fields)
{
	global $g_months;

	foreach ($fields as $name => $i)
	{
		if (isset($i["value"]))
		{
			if (isset($i["type"]) && $i["type"] == "file")
			{
				// ! ! !
			}
			else if (isset($i["type"]) && $i["type"] == "check")
			{
				$html->setvar($name, $i["value"] == 1 ? " checked" : "");
				$html->setvar($name . "_no", $i["value"] == 0 ? " checked" : "");
			}
			else if (isset($i["type"]) && $i["type"] == "checks")
			{
				rec_parse_checks($name, $db, &$html, $i["sql"], $i["value"]);
			}
			else if (isset($i["type"]) && $i["type"] == "checksOn")
			{
				rec_parse_checksOn($name, $db, &$html, $i["sql"], $i["value"]);
			}
			else if (isset($i["type"]) && ($i["type"] == "ilov" || $i["type"] == "slov") && isset($i["options"]))
			{
//				$opts = "";
//				foreach ($i["options"] as $v => $title)
//					$opts .= "<option value=\"" . $v . "\"" . (($v == ) ? " selected" : "") . ">" . $title . "</options>\n";
				$html->setvar($name . "Options", HSelectOptions($i["options"], $i["value"])	);
			}
			else if (isset($i["type"]) && ($i["type"] == "iselect" || $i["type"] == "sselect") && isset($i["sql"]))
			{
				$html->setvar($name . "Options", $db->DSelectOptions($i["sql"], $i["value"]));
			}
			else if (isset($i["type"]) && $i["type"] == "idblookup")
			{
				$s = $db->DLookUp($i["sql"] . to_sql($i["value"], "Number"));
				if ($s === 0) $s = "";
				$html->setvar($name, $s);
			}
			else if (isset($i["type"]) && $i["type"] == "lovlookup")
			{
				if (isset($i["options"][$i["value"]]))
					$html->setvar($name, $i["options"][$i["value"]]);
			}
			else if (isset($i["type"]) && $i["type"] == "date3")
			{
				$d = "";
				$m = "";
				$y = "";
				if ($i["value"] != "")
				{
					$aa = split("-", $i["value"]);
					$d = $aa[2];
					$m = $aa[1];
					$y = $aa[0];
				}
				$html->setvar($name . "DayOptions", NSelectOptions(1, 31, $d));
				$html->setvar($name . "MonthOptions", HSelectOptions($g_months, $m));
				$html->setvar($name . "YearOptions", NSelectOptions(1930, 2005, $y));
			} else {
//echo "<hr>$name : " . $i["value"] . "<hr>";
				$html->setvar($name, $i["value"]);
			}
		}
	}
}

// return the set of Javascript checks
// $form is the name of the <form>
function rec_html_checks(&$fields, $form)
{
	$ret = "";
	foreach ($fields as $name => $i)
	{
		if (! isset($i["nocheck"]))
		{
			if (isset($i["type"]) && (
				$i["type"] == "iselect" ||
				$i["type"] == "sselect" ||
				$i["type"] == "ilov" ||
				$i["type"] == "slov"
				) && (! isset($i["optional"])) )
			{
				$ret .= "sError += checkEmptyValue(\"" .
					(isset($i["title"]) ? $i["title"] : $name) .
					"\", document.forms[\"" . $form . "\"]." . $name . ".options[document.forms[\"" . $form . "\"]." . $name . ".selectedIndex].value" .
					");";
			}
			else if (isset($i["type"]) && $i["type"] == "file")
			{
				if (! isset($i["optional"]))
					$ret .= "sError += checkEmptyValue(\"" .
						(isset($i["title"]) ? $i["title"] : $name) .
						"\", document.forms[\"" . $form . "\"]." . $name . ".value" .
						");";
				// ! ! !
			}
			else if (isset($i["type"]) && $i["type"] == "date3")
			{
				if (! isset($i["optional"]))
				{
					$ret .= "sError += checkEmptyValue3(\"" .
						(isset($i["title"]) ? $i["title"] : $name) .
						"\", " .
						"document.forms[\"" . $form . "\"]." . $name . "Day.options[document.forms[\"" . $form . "\"]." . $name . "Day.selectedIndex].value," .
						"document.forms[\"" . $form . "\"]." . $name . "Month.options[document.forms[\"" . $form . "\"]." . $name . "Month.selectedIndex].value," .
						"document.forms[\"" . $form . "\"]." . $name . "Year.options[document.forms[\"" . $form . "\"]." . $name . "Year.selectedIndex].value" .
						");";
				}
			}
			else if (isset($i["type"]) && $i["type"] == "check")
			{
				if (! isset($i["optional"]))
					$ret .= "sError += checkCheckField(\"" .
						(isset($i["title"]) ? $i["title"] : $name) .
						"\", document.forms[\"" . $form . "\"]." . $name . ",1" .
						");";
			}
			else if (isset($i["type"]) && ($i["type"] == "checks" || $i["type"] == "checksOn"))
			{
				if (! isset($i["optional"]))
				{
					// ! ! !
				}
			}
			else if (isset($i["type"]) && $i["type"] == "int")
			{
				$ret .= "sError += checkIntField(\"" .
					(isset($i["title"]) ? $i["title"] : $name) .
					"\", document.forms[\"" . $form . "\"]." . $name . ".value," .
					((isset($i["optional"]) && $i["optional"] == 1) ? "false" : "true") .
					"," .
					(isset($i["min"]) ? $i["min"] : "NaN") .
					"," .
					(isset($i["max"]) ? $i["max"] : "NaN") .
					");";
			}
			else if (isset($i["type"]) && $i["type"] == "float")
			{
				$ret .= "sError += checkFloatField(\"" .
					(isset($i["title"]) ? $i["title"] : $name) .
					"\", document.forms[\"" . $form . "\"]." . $name . ".value," .
					((isset($i["optional"]) && $i["optional"] == 1) ? "false" : "true") .
					"," .
					(isset($i["min"]) ? $i["min"] : "NaN") .
					"," .
					(isset($i["max"]) ? $i["max"] : "NaN") .
					");";
			} else {
				$ret .= "sError += checkField(\"" .
					(isset($i["title"]) ? $i["title"] : $name) .
					"\", document.forms[\"" . $form . "\"]." . $name . ".value," .
					((isset($i["optional"]) && $i["optional"] == 1) ? "false" : "true") .
					"," .
					(isset($i["min"]) ? $i["min"] : "NaN") .
					"," .
					(isset($i["max"]) ? $i["max"] : "NaN") .
					");";
			}
		
			$ret .= "\n";
		}
	}
	return $ret;
}







class CHtmlRecord extends CHtmlBlock
{
	var $m_db = null;
	var $m_fields = Array();

	var $m_bInsert = 1;
	var $m_bUpdate = 1;
	var $m_bDelete = 1;

	var $m_id = 0;

	var $m_table;
	var $m_return_page = "";
	var $m_sqlFromWhere = "";

	var $sMessage = "";


	function CHtmlRecord($db, $name, $html_path, $table, $sqlFromWhere, $return_page)
	{
		$this->CHtmlBlock($name, $html_path);
		$this->m_db = $db;
		$this->m_sqlFromWhere = $sqlFromWhere;
		$this->m_table = $table;
		$this->m_return_page = $return_page;
	}


	function customValidate($cmd)
	{
		return "";
	}

	function customAction($cmd)
	{
		return "";
	}


	function init()
	{

		$this->m_id = get_param($this->m_name . "Id", $this->m_id);
		if ($this->m_id == "")
			$this->m_id = 0;

		if ($this->m_id != 0)
		{
			if (! rec_get_db($this->m_db, $this->m_fields, $this->m_sqlFromWhere .  to_sql($this->m_id, "Number")))
			{
				$this->sMessage = "Такой записи в базе данных нет.<br>";
			} else {
//				foreach ($this->m_fields as $n=>$v)
//				{
//					echo "<hr>$n : " . $v["value"] . "<hr>";
//				}
			}
		}
		parent::init();
	}


	function action()
	{

		$cmd = get_param("cmd", "");

		if ($cmd == $this->m_name . "_insert" || $cmd == $this->m_name . "_update")
		{
			if ( (($this->m_id == 0) && (! $this->m_bInsert)) || (($this->m_id > 0) && (! $this->m_bUpdate)) )
			{
				header("Location: " . $this->m_return_page . get_params() . "\n");
				exit;			
			}

			$this->sMessage .= rec_get_http($this->m_db, $this->m_fields, $this->m_id, $this->m_name, $this->m_table);

			$this->sMessage .= $this->customValidate(&$cmd);

			if ($this->sMessage == "" && ($cmd == $this->m_name . "_insert" || $cmd == $this->m_name . "_update"))
			{

				$sql = "";
				if ($this->m_id == 0)
				{
					$sResult = "added";
					$sql = "INSERT INTO " . $this->m_table . " (" . rec_sql_fields($this->m_fields) . ") values (" . rec_sql_values($this->m_fields) . ")";
				} else {
					$sResult = "updated";
					$sql = "UPDATE " . $this->m_table . " SET " . rec_fields_to_sql($this->m_fields) .
						" WHERE " . $this->m_name . "Id=" . to_sql($this->m_id, "Number");
				}

//echo "<hr>$sql<hr>";

				if ($this->m_db->execute($sql))
				{
					if ($this->m_id == 0)
					{
						$this->m_id = $this->m_db->get_insert_id();
					}

					$this->sMessage .= $this->customAction(&$cmd);

					if ($this->sMessage == "" && ($cmd == $this->m_name . "_insert" || $cmd == $this->m_name . "_update"))
					{
						header("Location: " . $this->m_return_page . "res=" . $sResult . "&" . get_params() . "\n");
						exit;
					}
				}
			}
		} else {
			$this->sMessage .= $this->customValidate(&$cmd);
		}

		$this->sMessage .= $this->customAction(&$cmd);

		if ($cmd == "delete")
		{
			if (! $this->m_bDelete)
			{
				header("Location: " . $this->m_return_page . get_params() . "\n");
				exit;			
			}
			if ($this->m_id != 0)
			{
				$this->m_db->execute("DELETE FROM " . $this->m_table . " WHERE " . $this->m_name . "Id=" . to_sql($this->m_id, "Number"));
				
				header("Location: " . $this->m_return_page . "res=deleted&" . get_params() . "\n");
				exit;			
			}			
		}


	}


	function parseBlock(&$html)
	{

		$html->setvar($this->m_name . "Id", $this->m_id);
		$html->setvar("cmd", $this->m_id == 0 ? "insert" : "update");

		if ($this->m_id == 0)
		{
			if ($html->blockexists($this->m_name . "_bHeadNew"))
				$html->parse($this->m_name . "_bHeadNew");
			//$html->setvar($this->m_name . "_bHeadExisted", "");

			if ($this->m_bInsert && $html->blockexists($this->m_name . "_bInsert"))
				$html->parse($this->m_name . "_bInsert");
		}
		else
		{
			//$html->setvar($this->m_name . "_bHeadNew", "");
			if ($html->blockexists($this->m_name . "_bHeadExisted"))
				$html->parse($this->m_name . "_bHeadExisted");

			if ($this->m_bDelete && $html->blockexists($this->m_name . "_bDelete"))
				$html->parse($this->m_name . "_bDelete");
			if ($this->m_bUpdate && $html->blockexists($this->m_name . "_bUpdate"))
				$html->parse($this->m_name . "_bUpdate");
		}

		if ($this->sMessage != "")
		{
			$html->setvar("sMessage", $this->sMessage);
			if ($html->blockexists($this->m_name . "_bMessage"))
				$html->parse($this->m_name . "_bMessage");
		}


		rec_parse_values($this->m_db, $html, $this->m_fields);

		$html->setvar("checks", rec_html_checks($this->m_fields, $this->m_name));


		parent::parseBlock(&$html);
	}


}

?>
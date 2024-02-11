<?php


include_once("../include/core.php");
include_once("../include/db.php");
include_once("../include/html.php");
include_once("../include/params.php");
include_once("../include/block.php");
include_once("../include/image.php");
include_once("../include/common.php");
include_once("../include/admin.php");
include_once("../include/record.php");
include_once("../include/grid.php");

get_block_params("user");

class CProfileForm extends CHtmlRecord
{
	function customValidate($cmd)
	{
	}
	function customAction($cmd)
	{
		if ($cmd == "delete")
		{
			deleteFotos($this->m_db, $this->m_id);
		}
		if ($cmd == "delemail")
		{
			$userId = get_param("userId", 0);
			$whoId = get_param("whoId", 0);
			if ($whoId > 0 && $userId > 0)
				$this->m_db->execute("DELETE FROM emails WHERE userId2=" . to_sql($userId, "Number") . " AND userId1=" . to_sql($whoId, "Number"));
		}
	}
}


class CPics extends CHtmlBlock
{
	var $m_db = null;
	var $m_userId = 0;
	var $sMessage = 0;

	function CPics($db, $name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
		$this->m_db = $db;

		$this->m_userId = get_param("userId", 0);
	}

	function action()
	{
		$cmd = get_param("cmd", "");

		if ($cmd == "pics" && $this->m_userId > 0)
		{
			$a = get_param_array("picId");
			$picIds = implode(",", $a);
			if ($picIds != "")
			{
				$this->m_db->execute("UPDATE pics SET bApproved='Y' WHERE picId IN (" . $picIds . ") AND userId=" . to_sql($this->m_userId, "Number"));
				$this->m_db->execute("UPDATE pics SET bApproved='N' WHERE picId NOT IN (" . $picIds . ") AND userId=" . to_sql($this->m_userId, "Number"));
				$picId = $this->m_db->DLookUp("SELECT picId FROM pics WHERE bMain='Y' AND userId=" . to_sql($this->m_userId, "Number"));
				if ($picId > 0 && $this->m_db->DLookUp("SELECT bApproved FROM pics WHERE picId=" . to_sql($picId, "Number") . " AND userId=" . to_sql($this->m_userId, "Number")) == "N")
					$this->m_db->execute("UPDATE pics SET bMain='N' WHERE picId=" . to_sql($picId, "Number") . " AND userId=" . to_sql($this->m_userId, "Number"));
				$picId = $this->m_db->DLookUp("SELECT picId FROM pics WHERE bApproved='Y' AND userId=" . to_sql($this->m_userId, "Number"));
				if ($picId > 0)
					$this->m_db->execute("UPDATE pics SET bMain='Y' WHERE picId=" . to_sql($picId, "Number") . " AND userId=" . to_sql($this->m_userId, "Number"));
			} else {
				$this->m_db->execute("UPDATE pics SET bApproved='N', bMain='N' WHERE userId=" . to_sql($this->m_userId, "Number"));
			}
	        $this->sMessage = "Обновления приняты";
		}
	}

	function parseBlock(&$html)
	{

		if ($this->m_userId > 0)
		{
			$this->m_db->query("SELECT picId, bApproved FROM pics WHERE userId=" . to_sql($this->m_userId, "Number"));
			while ($row = $this->m_db->fetch_row())
			{
				$html->setvar("userId", $this->m_userId);
				$html->setvar("picId", $row["picId"]);
				$html->setvar("checked", ($row["bApproved"]=="Y" ? "checked" : ""));
				$html->parse("pic", true);
			}
			$this->m_db->free_result();
		}

		if ($this->sMessage != "")
		{
			$html->setvar("sMessage", $this->sMessage);
			if ($html->blockexists($this->m_name . "_bMessage"))
				$html->parse($this->m_name . "_bMessage");
		}

		parent::parseBlock(&$html);
	}
}



$db = new CDB();
$db->connect();
loadOptions($db);

$page = new CAdminPage("", "../html/admin/user.html");
$page->add(new CAdminHeader("iHeader", "../html/admin/header.html"));
$page->add(new CHtmlBlock("iFooter", "../html/admin/footer.html"));

$user = new CProfileForm($db, "user", null, "users", "FROM users WHERE userId=", "users.php?");
$user->m_fields["login"] = Array ("title" => "Логин", "value" => "", "min" => 2, "max" => 32, "noupdate"=>1, "nohttp"=>1, "nocheck"=>1);
$user->m_fields["passwd"] = Array ("title" => "Пароль", "value" => "", "min" => 5, "max" => 32, "optional" => 1);
$user->m_fields["email"] = Array ("title" => "Email", "value" => "", "min" => 7, "max" => 80);
$user->m_fields["sex"] = Array ("title" => "Ваш пол", "type" => "slov", "value" => "", "options" => $g_sex);
$user->m_fields["lookSex"] = Array ("title" => "Искомый пол", "type" => "slov", "value" => "", "options" => $g_lookSex);
$user->m_fields["cityId"] = Array ("title" => "Город", "type"=>"iselect", "value" => "", "sql"=>"SELECT cityId, title FROM cities ORDER BY priority,title", "sqlcheck"=>"SELECT count(*) FROM cities WHERE cityId=");
$user->m_fields["birth"] = Array ("title" => "День рождения", "type"=>"date3", "value" => "");
$user->m_fields["hight"] = Array ("title" => "Рост", "type"=>"int", "value" => "", "min" => 10, "max" => 300, "optional"=>1);
$user->m_fields["weight"] = Array ("title" => "Вес", "type"=>"int", "value" => "", "min" => 10, "max" => 300, "optional"=>1);
$user->m_fields["goalIds"] = Array ("title" => "Цели знакомства", "type"=>"checks", "value" => "0", "sql"=>"SELECT maskId, title FROM goals ORDER BY priority", "optional"=>1);
$user->m_fields["moneyId"] = Array ("title" => "Материальное положение", "type"=>"iselect", "value" => "", "sql"=>"SELECT moneyId, title FROM money ORDER BY priority", "sqlcheck"=>"SELECT count(*) FROM money WHERE moneyId=", "optional"=>1);
$user->m_fields["childrenId"] = Array ("title" => "Дети", "type" => "slov", "value" => "", "options" => $g_children, "optional"=>1);
$user->m_fields["bMarried"] = Array ("title" => "Семейное положение", "type"=>"check", "value" => "0", "optional" => 1);
$user->m_fields["homeId"] = Array ("title" => "Проживание", "type"=>"iselect", "value" => "", "sql"=>"SELECT homeId, title FROM homes ORDER BY priority", "sqlcheck"=>"SELECT count(*) FROM homes WHERE homeId=", "optional"=>1);
$user->m_fields["langIds"] = Array ("title" => "Знание языков", "type"=>"checks", "value"=>"0", "sql"=>"SELECT maskId, title FROM langs ORDER BY priority", "optional"=>1);
$user->m_fields["interesIds"] = Array ("title" => "Интересы", "type"=>"checks", "value"=>"0", "sql"=>"SELECT maskId, title FROM intereses ORDER BY priority", "optional"=>1);
$user->m_fields["alcoholId"] = Array ("title" => "Алкоголь", "type"=>"iselect", "value" => "", "sql"=>"SELECT alcoholId, title FROM alcohols ORDER BY priority", "sqlcheck"=>"SELECT count(*) FROM alcohols WHERE alcoholId=", "optional"=>1);
$user->m_fields["smokeId"] = Array ("title" => "Курение", "type"=>"iselect", "value" => "", "sql"=>"SELECT smokeId, title FROM smokes ORDER BY priority", "sqlcheck"=>"SELECT count(*) FROM smokes WHERE smokeId=", "optional"=>1);
$user->m_fields["about"] = Array ("title" => "Обо мне", "value" => "", "min" => 1, "max" => 10240, "optional"=>1);
$user->m_fields["bVIP"] = Array ("title" => "VIP", "type"=>"check", "value" => "0", "optional" => 1);
$user->m_fields["account"] = Array ("title" => "Счет", "type"=>"float", "value" => "0.0", "min" => 0.0);
$user->m_fields["registered"] = Array ("plain" => "now()", "type"=>"plain", "noupdate"=>1, "nohttp"=>1, "nocheck"=>1);
$user->m_fields["lastAccess"] = Array ("title" => "Последний доступ", "value" => "", "min" => 8, "max" => 64, "optional" => 1);
$user->m_id = get_session("_userId");
$page->add($user);

$userId = get_param("userId", 0);
if ($userId > 0)
{
	$page->add(new CPics($db, "pics", null));

	$emails = new CHtmlGrid($db, "emails", null);
	$emails->m_sqlcount = "select count(*) as cnt from emails where userId2=" . to_sql($userId, "Number");
	$emails->m_sql = "select userId1 as whoId,login from emails AS e LEFT JOIN users AS u ON u.userId=e.userId1 where userId2=" . to_sql($userId, "Number");
	$emails->m_fields["whoId"] = Array ("whoId", null, "");
	$emails->m_fields["login"] = Array ("login", null, "");
	$page->add($emails);
}

$page->init();
$page->action();
$page->parse(null);



?>
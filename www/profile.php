<?php


include_once("include/core.php");
include_once("include/db.php");
include_once("include/html.php");
include_once("include/params.php");
include_once("include/block.php");
include_once("include/image.php");
include_once("include/grid.php");
include_once("include/record.php");
include_once("include/common.php");
include_once("include/public.php");


class CProfileForm extends CHtmlRecord
{

	function customValidate($cmd)
	{
		if ($cmd == $this->m_name . "_update")
		{
			if ($this->m_fields["passwd"]["value"] == "")
				$this->m_fields["passwd"]["noupdate"] = 1;
		}
	}
	function customAction($cmd)
	{
		if ($cmd == $this->m_name . "_update")
		{
			if ($this->sMessage == "")
				$this->sMessage = "Изменения приняты";
				
			#set_session("_cityId", $_POST["city"]);	
		}
	}
}

class CPhotoTop extends CHtmlBlock
{
	var $m_db = null;
	var $sMessage = "";

	function CPhotoTop($db, $name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
		$this->m_db = $db;
	}
	
	function parseBlock(&$html)
	{
		global $g_options;

		$userId = get_session("_userId");
		$html->setvar("userId", $userId);
		
			$html->setvar("login", get_session("_login"));
			$html->setvar("sex", get_session("_sex"));
			$html->setvar("city", get_session("_city"));
			$html->setvar("sex", get_session("_sex"));
			$html->setvar("age", get_session("_age"));
		
		$html->setvar("MAX_FILE_SIZE", $g_options["picSize"] * 1024);
		$n = 0;
		if ($this->m_db->query("SELECT picId,bMain,bApproved FROM pics WHERE userId=" . to_sql($userId, "Number")))
		{
			while($row = $this->m_db->fetch_row())
			{
				$html->setvar("picId", $row["picId"]);
				$html->setvar("userId", $userId);
				if ($row["bMain"] == "Y")
					$html->setvar("bMain", " checked");
				else
					$html->setvar("bMain", "");
				$html->setvar("n", $n+1);
				$html->setvar("sApproved", ($row["bApproved"]=="Y" ? "" : "Фото заблокировано администратором"));
				$html->parse("phototop", true);
				$n++;
			}
			$this->m_db->free_result();
		}

		if ($n < $g_options["picNum"])
		{
			for ($i = 0; $i < $g_options["picNum"] - $n; $i++) $html->parse("nophototop");
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



class CUpForm extends CHtmlBlock
{
	var $m_db = null;
	var $sMessage = "";

	function CUpForm($db, $name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
		$this->m_db = $db;
	}

	function action()
	{
		global $g_options;
		$userId = get_session("_userId");

		$cmd = get_param("cmd", "");
		if ($cmd == "upfree")
		{
			$daysForFree = $g_options["ratingFreezeDays"];
			$bMayFree = $this->m_db->DLookUp("SELECT if(DATE_SUB(now(),INTERVAL " . to_sql($daysForFree, "Number") . " DAY) < freeUpDate, 0, 1) FROM users WHERE userId=" . to_sql($userId, "Number"));
			if ($bMayFree)
			{
				$this->m_db->execute("UPDATE users SET rating=20,freeUpDate=now() WHERE userId=" . to_sql($userId, "Number"));
				$this->sMessage = "Ваш рейтинг поднят.";
			}
		}
		if ($cmd == "upmoney")
		{
			$account = $this->m_db->DLookUp("SELECT account FROM users WHERE userId=" . to_sql($userId, "Number"));
			if ($g_options["ratingUpPrice"] > $account)
			{
				$this->sMessage = "У вас недостаточно денег на счету.";
			} else {
				$this->m_db->execute("UPDATE users SET rating=20,account=account-" . to_sql($g_options["ratingUpPrice"], "Number") . " WHERE userId=" . to_sql($userId, "Number"));
				$this->sMessage = "Ваш рейтинг поднят.";
			}
		}
	}	


	function parseBlock(&$html)
	{
		global $g_options;
		$userId = get_session("_userId");

		$daysForFree = $g_options["ratingFreezeDays"];
		$bMayFree = $this->m_db->DLookUp("SELECT if(DATE_SUB(now(),INTERVAL " . to_sql($daysForFree, "Number") . " DAY) < freeUpDate, 0, 1) FROM users WHERE userId=" . to_sql($userId, "Number"));
		
		$bUp = false;
		if ($bMayFree)
			$bUp = true;
		else
		{
			if ($g_options["ratingUpPrice"] > 0)
				$bUp = true;
		}

		$html->setvar("account", $this->m_db->DLookUp("SELECT account FROM users WHERE userId=" . to_sql($userId, "Number")));

		if ($bUp)
		{
			if ($bMayFree)
			{
				$html->parsesafe("up_free");
			} else {
				$html->setvar("ratingUpPrice", $g_options["ratingUpPrice"]);
				$html->parsesafe("up_money");
			}

		}

		if ($this->sMessage != "")
		{
			$html->setvar("sMessage", $this->sMessage);
			$html->parsesafe("up_bMessage");
		}

		parent::parseBlock(&$html);
	}


}

$db = new CDB();
$db->connect();
loadOptions($db);

$page = new CLoggedPage("", "html/" . $g_theme . "/profile.html");
$page->add(new CCommonHeader($db, "iHeader", "html/" . $g_theme . "/header.html"));
$page->add(new CHtmlBlock("iFooter", "html/" . $g_theme . "/footer.html"));

$user = new CProfileForm($db, "user", null, "users", "FROM users WHERE userId=", "message.php?mes=reg1&");
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
$user->m_fields["bVIP"] = Array ("title" => "VIP", "type"=>"lovlookup", "value" => "N", "options"=>$g_vip, "nocheck"=>1);
$user->m_fields["account"] = Array ("title" => "Счет", "type"=>"float", "value" => "0.0", "min" => 0, "nocheck"=>1);
$user->m_fields["registered"] = Array ("plain" => "now()", "type"=>"plain", "noupdate"=>1, "nohttp"=>1, "nocheck"=>1);
$user->m_id = get_session("_userId");
$page->add($user);

$photo_top = new CPhotoTop($db, "fPhotoTop", null);
$page->add($photo_top);

class CAccount extends CHtmlBlock
{
	var $m_db = null;
	var $sMessage = "";

	function CAccount($db, $name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
		$this->m_db = $db;
	}
	
	function parseBlock(&$html)
	{
		global $g_options;

		$userId = get_session("_userId");
		$html->setvar("userId", $userId);

		if ($this->m_db->query("SELECT account, login FROM users WHERE userId=" . to_sql($userId, "Number")))
		{
			$row = $this->m_db->fetch_row();
				$html->setvar("account", $row["account"]);
			$this->m_db->free_result();
		}

		parent::parseBlock(&$html);
	}


}
$account = new CAccount($db, "fAccount", null);
$page->add($account);


$up = new CUpForm($db, "up", null);
$page->add($up);

$page->init();
$page->action();
$page->parse(null);



?>
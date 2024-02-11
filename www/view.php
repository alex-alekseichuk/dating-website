<?php


include_once("include/core.php");
include_once("include/db.php");
include_once("include/html.php");
include_once("include/params.php");
include_once("include/block.php");
include_once("include/image.php");
include_once("include/record.php");
include_once("include/grid.php");
include_once("include/common.php");
include_once("include/public.php");


$db = new CDB();
$db->connect();
loadOptions($db);

$userId = get_param("userId", 0);

if ($userId == 0)
{
	header("Location: index.php\n");
	exit;
}


class CProfileForm extends CHtmlRecord
{
	function action()
	{
		global $userId;

		$cmd = get_param("cmd", "");
		
		if ($cmd == "openemail")
		{
			$myId = get_session("_userId");
			if ($myId != $userId)
			{
				if (0 == $this->m_db->DLookUp("SELECT count(*) AS cnt FROM userId1=" . to_sql($myId, "Number") . " AND userId2=" . to_sql($userId, "Number")))
				{
					$emailPrice = $this->m_db->DLookUp("SELECT emailPrice FROM options");
					$account = $this->m_db->DLookUp("SELECT account FROM users WHERE userId=" . to_sql($myId, "Number"));
					if ($account >= $emailPrice)
					{
						$this->m_db->execute("UPDATE users SET account=account-" . to_sql($emailPrice, "Number") . " WHERE userId=" . to_sql($myId, "Number"));
						$this->m_db->execute("INSERT INTO emails (userId1, userId2) VALUES (" . to_sql($myId, "Number") . "," . to_sql($userId, "Number") . ")");
					} else {
						$this->sMessage = "Недостаточно денег на счету";
					}
				}
			}
		}

		parent::action();
	}

	function parseBlock(&$html)
	{
		global $g_options;
		global $g_m_married;
		global $g_f_married;
		global $userId;

		$bMaried = $this->m_fields["bMarried"]["value"] ? "Y" : "N";
		$html->setvar("married", 
			$this->m_fields["sex"]["value"] == "M"
			?
				$g_m_married[$bMaried]
			:
				$g_f_married[$bMaried]
		);

		if ($this->m_fields["hight"]["value"] == "")
			$this->m_fields["hight"]["value"] = "неуказан";
		if ($this->m_fields["weight"]["value"] == "")
			$this->m_fields["weight"]["value"] = "неуказан";

		$bEmail = false;
		$emailPrice = 0;
		$myId = get_session("_userId");
		$nOpenedEmail = $this->m_db->DLookUp("SELECT count(*) AS cnt FROM emails WHERE userId1=" . to_sql($myId, "Number") . " AND userId2=" . to_sql($userId, "Number"));
		if ($nOpenedEmail == 1)
			$bEmail = true;
		else
		{
//			$emailPrice = $this->m_db->DLookUp("SELECT emailPrice FROM options");
			$emailPrice = $g_options["emailPrice"];
			$account = $this->m_db->DLookUp("SELECT account FROM users WHERE userId=" . to_sql($myId, "Number"));
			if ($emailPrice > 0)
				$bEmail = true;
		}

		if ($bEmail)
		{
			$html->setvar("userId", $userId);
			if ($nOpenedEmail == 1)
			{
				$html->setvar("email", $this->m_db->DLookUp("SELECT email FROM users WHERE userId=" . to_sql($userId, "Number")));
				$html->parsesafe("open_email");
			} else {
				$html->setvar("emailPrice", $emailPrice);
				$html->setvar("account", $account);
				$html->parsesafe("close_email");
			}
			$html->parsesafe("email");
		}


		parent::parseBlock(&$html);
	}
}



$bVIP = false;
if ($db->DLookUp("SELECT bVIP FROM users WHERE userId=" . to_sql($userId, "Number")) == "Y")
{
	$view_file = "view_vip.html";
	$bVIP = true;
} else {
	$view_file = "view.html";
}

$db->execute("UPDATE users SET nViews=nViews+1 WHERE userId=" . to_sql($userId, "Number"));

$page = new CCommonPage("", "html/" . $g_theme . "/" . $view_file);
$page->add(new CCommonHeader($db, "iHeader", "html/" . $g_theme . "/header.html"));
$page->add(new CHtmlBlock("iFooter", "html/" . $g_theme . "/footer.html"));

$user = new CProfileForm($db, "user", null, "users", "FROM users WHERE userId=", "");
$user->m_fields["login"] = Array ("title" => "Логин", "value" => "", "min" => 2, "max" => 32, "noupdate"=>1, "nohttp"=>1, "nocheck"=>1);
$user->m_fields["email"] = Array ("title" => "Email", "value" => "", "min" => 7, "max" => 80);
$user->m_fields["sex"] = Array ("title" => "Ваш пол", "type" => "lovlookup", "value" => "", "options" => $g_sex);
$user->m_fields["lookSex"] = Array ("title" => "Искомый пол", "type" => "lovlookup", "value" => "", "options" => $g_lookSex);
$user->m_fields["cityId"] = Array ("title" => "Город", "type" => "idblookup", "value" => "", "sql" => "SELECT title FROM cities WHERE cityId=");
$user->m_fields["birth"] = Array ("title" => "День рождения", "type"=>"date3", "value" => "");
$user->m_fields["hight"] = Array ("title" => "Рост", "type"=>"int", "value" => "", "min" => 10, "max" => 300, "optional"=>1);
$user->m_fields["weight"] = Array ("title" => "Вес", "type"=>"int", "value" => "", "min" => 10, "max" => 300, "optional"=>1);
$user->m_fields["goalIds"] = Array ("title" => "Цели знакомства", "type"=>"checksOn", "value" => "0", "sql"=>"SELECT maskId, title FROM goals ORDER BY priority", "optional"=>1);
$user->m_fields["moneyId"] = Array ("title" => "Материальное положение", "type"=>"idblookup", "value" => "", "sql"=>"SELECT title FROM money WHERE moneyId=");
$user->m_fields["childrenId"] = Array ("title" => "Дети", "type" => "lovlookup", "value" => "", "options" => $g_children, "optional"=>1);
$user->m_fields["bMarried"] = Array ("title" => "Семейное положение", "type"=>"check", "value" => "0", "optional" => 1);
$user->m_fields["homeId"] = Array ("title" => "Проживание", "type"=>"idblookup", "value" => "", "sql"=>"SELECT title FROM homes WHERE homeId=", "optional"=>1);
$user->m_fields["langIds"] = Array ("title" => "Знание языков", "type"=>"checksOn", "value"=>"0", "sql"=>"SELECT maskId, title FROM langs ORDER BY priority", "optional"=>1);
$user->m_fields["interesIds"] = Array ("title" => "Интересы", "type"=>"checksOn", "value"=>"0", "sql"=>"SELECT maskId, title FROM intereses ORDER BY priority", "optional"=>1);
$user->m_fields["alcoholId"] = Array ("title" => "Алкоголь", "type"=>"idblookup", "value" => "", "sql"=>"SELECT title FROM alcohols WHERE alcoholId=", "optional"=>1);
$user->m_fields["smokeId"] = Array ("title" => "Курение", "type"=>"idblookup", "value" => "", "sql"=>"SELECT title FROM smokes WHERE smokeId=", "optional"=>1);
$user->m_fields["about"] = Array ("title" => "Обо мне", "value" => "", "min" => 1, "max" => 10240, "optional"=>1);
$user->m_fields["age"] = Array ("title"=>"Возраст", "type"=>"int", "dbSelect"=>"floor((TO_DAYS(now())-TO_DAYS(birth))/365) as age", "value"=>"0");
$page->add($user);

$pics = new CHtmlGrid($db, "pics", null);
$pics->m_sqlcount = "select count(*) as cnt from pics where bApproved='Y' and userId=" . to_sql($userId, "Number");
$pics->m_sql = "select picId from pics where bApproved='Y' and userId=" . to_sql($userId, "Number");
$pics->m_fields["picId"] = Array ("picId", null, "");
$pics->m_fields["userId"] = Array ("userId", $userId, "");
$pics->m_fields["width"] = Array ("width", (IMAGE_BIG_X + 20), "");
$pics->m_fields["height"] = Array ("height", (IMAGE_BIG_Y + 20), "");
$page->add($pics);


if ($g_options["videoVIP"] != 1 || $bVIP)
{
	$videos = new CHtmlGrid($db, "videos", null);
	$videos->m_sqlcount = "select count(*) as cnt from videos where userId=" . to_sql($userId, "Number");
	$videos->m_sql = "select videoId, video from videos where userId=" . to_sql($userId, "Number");
	$videos->m_fields["videoId"] = Array ("videoId", null, "");
	$videos->m_fields["video"] = Array ("video", null, "");
	$videos->m_fields["userId"] = Array ("userId", $userId, "");
	$page->add($videos);
}

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

$page->init();
$page->action();
$page->parse(null);



?>
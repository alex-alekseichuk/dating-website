<?php


include_once("include/core.php");
include_once("include/db.php");
include_once("include/html.php");
include_once("include/params.php");
include_once("include/block.php");
include_once("include/image.php");
include_once("include/grid.php");
include_once("include/common.php");
include_once("include/public.php");
include_once("include/record.php");

get_block_params("pCompose");

$db = new CDB();
$db->connect();
loadOptions($db);


$userId = get_session("_userId");
$toId = get_param("userId", 0);

if ($userId == $toId || $toId == 0)
{
	header("Location: mail.php?" . get_params() . "\n");
	exit;	
}



$db->execute("UPDATE messages SET bNew='N' WHERE fromId=" . to_sql($toId, "Number") . " AND userId=" . to_sql($userId, "Number"));



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

class CUserForm extends CHtmlBlock
{
	var $m_db = null;

	function CUserForm($db, $name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
		$this->m_db = $db;
	}

	function parseBlock(&$html)
	{
		global $toId;
		global $userId;

		$picId = $this->m_db->DLookUp("SELECT picId FROM pics WHERE bMain='Y' AND bApproved='Y' AND userId=" . to_sql($toId, "Number"));
		if ($picId > 0)
			$img = $toId . "_" . $picId . "_s.jpg";
		else
			$img = "no.jpg";
		$login = $this->m_db->DLookUp("SELECT login FROM users WHERE userId=" . to_sql($toId, "Number"));
		$age = $this->m_db->DLookUp("SELECT floor((TO_DAYS(now())-TO_DAYS(u.birth))/365) as age FROM users WHERE userId=" . to_sql($toId, "Number"));
		$sex = $this->m_db->DLookUp("SELECT IF(sex='M', 'мужской', 'женский') AS sex FROM users WHERE userId=" . to_sql($toId, "Number"));

		$html->setvar("userId", $toId);
		$html->setvar("login", $login);
		$html->setvar("age", $age);
		$html->setvar("sex", $sex);
		$html->setvar("img", $img);

		if ($this->m_db->DLookUp("SELECT count(*) FROM links WHERE linkId=1 AND userId1=" . to_sql($toId, "Number") . " AND userId2=" . to_sql($userId, "Number"))> 0)
			$html->parse("bBlacklist");

		parent::parseBlock(&$html);
	}

}

class CComposeForm extends CHtmlRecord
{

	function customValidate($cmd)
	{
		global $toId;
		global $userId;

		if ($this->m_db->DLookUp("SELECT count(*) FROM links WHERE linkId=1 AND userId1=" . to_sql($toId, "Number") . " AND userId2=" . to_sql($userId, "Number"))> 0)
			return "Нельзя отослать сообщение. Вы в черном списке этого человека.";
	}

	function customAction($cmd)
	{
		global $toId;
		global $userId;

		if ($cmd == $this->m_name . "_insert")
		{
			$this->m_db->execute("UPDATE users SET nMessages=nMessages+1 WHERE userId=" . to_sql($toId, "Number"));

			if ($this->m_db->DLookUp("SELECT count(*) FROM links WHERE userId1=" . to_sql($toId, "Number") . " AND userId2=" . to_sql($userId, "Number")) == 0)
			{
				$this->m_db->execute("INSERT INTO links (userId1, userId2, linkId) VALUES (" .
					to_sql($toId, "Number") . "," .
					to_sql($userId, "Number") . "," .
					"0)");
				$this->m_db->execute("INSERT INTO links (userId1, userId2, linkId) VALUES (" .
					to_sql($userId, "Number") . "," .
					to_sql($toId, "Number") . "," .
					"0)");
			}
		}
		return "";
	}


}

class CContactsGrid extends CHtmlGrid
{
	function onItem()
	{
		$this->m_fields["message"][2] = to_html($this->m_fields["message"][2]);
	}	
}


$page = new CLoggedPage("", "html/" . $g_theme . "/compose.html");
$page->add(new CCommonHeader($db, "iHeader", "html/" . $g_theme . "/header.html"));
$page->add(new CHtmlBlock("iFooter", "html/" . $g_theme . "/footer.html"));


$user = new CUserForm($db, "user", null);
$page->add($user);

$messages = new CContactsGrid($db, "messages", null);
$messages->m_sqlcount = "select count(*) as cnt from messages" . 
	" where (fromId=" . to_sql($userId, "Number") . " AND userId=" . to_sql($toId, "Number") . ")" .
	" OR (fromId=" . to_sql($toId, "Number") . " AND userId=" . to_sql($userId, "Number") . ")";
$messages->m_sql = "select m.fromId,u.login as login,m.sent,m.message" .
	" from messages as m left join users as u on m.fromId=u.userId" .
	" where (m.fromId=" . to_sql($userId, "Number") . " AND m.userId=" . to_sql($toId, "Number") . ")" .
	" OR (m.fromId=" . to_sql($toId, "Number") . " AND m.userId=" . to_sql($userId, "Number") . ")";
//debug($messages->m_sql);
$messages->m_fields["fromId"] = Array ("fromId", null, "");
$messages->m_fields["login"] = Array ("login", null, "");
$messages->m_fields["message"] = Array ("message", null, "");
$messages->m_fields["sent"] = Array ("sent", null, "");
$messages->m_sort = "sent";
$messages->m_dir = "asc";
$messages->m_pageMode = 2; // 2-last page should be filled
$messages->m_lastPageByDefault = 1; // 1-views last page by default
$page->add($messages);

if ($db->DLookUp("SELECT count(*) FROM links WHERE linkId=1 AND" .
	" ((userId1=" . to_sql($toId, "Number") . " AND userId2=" . to_sql($userId, "Number") .
	") OR (userId1=" . to_sql($userId, "Number") . " AND userId2=" . to_sql($toId, "Number") .
	"))"
) == 0)
{
	$compose = new CComposeForm($db, "compose", null, "messages", "FROM messages WHERE messageId=", "compose.php?");
	$compose->m_fields["message"] = Array ("title"=>"Сообщение", "value"=>"", "min"=>0, "max"=>4096);
	$compose->m_fields["userId"] = Array ("type"=>"int", "value"=>$toId, "noupdate"=>1, "nocheck"=>1);
	$compose->m_fields["fromId"] = Array ("type"=>"int", "value"=>$userId, "noupdate"=>1, "nohttp"=>1, "nocheck"=>1);
	$compose->m_fields["sent"] = Array ("plain" => "now()", "type"=>"plain", "noupdate"=>1, "nohttp"=>1, "nocheck"=>1);
	$page->add($compose);
}

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

$page->init();
$page->action();
$page->parse(null);


?>
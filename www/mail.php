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

get_block_params("pMail");

$sMessage = "";

$db = new CDB();
$db->connect();
loadOptions($db);



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

class CContactsGrid extends CHtmlGrid
{
	function onItem()
	{
		if ($this->m_fields["nMess"][2] > 0)
		{
			$this->m_itemBlocks["bNew"] = 1;
			$this->m_itemBlocks["bIncome"] = 1;
		} else {
			$this->m_itemBlocks["bNew"] = 0;
			$this->m_itemBlocks["bIncome"] = 0;
		}
	}	
}


$page = new CLoggedPage("", "html/" . $g_theme . "/mail.html");
$page->add(new CCommonHeader($db, "iHeader", "html/" . $g_theme . "/header.html"));
$page->add(new CHtmlBlock("iFooter", "html/" . $g_theme . "/footer.html"));

$userId = get_session("_userId");
$contacts = new CContactsGrid($db, "contacts", null);
$contacts->m_sqlcount = "select count(*) as cnt from (links as l left join users as u on u.userId=l.userId2) where l.userId1=" . to_sql($userId, "Number") . " AND u.userId IS NOT NULL AND linkId=0";
/*$contacts->m_sql = "select u.userId,u.login as login,count(m.messageId) as nMess" .
	" from (links as l left join users as u on u.userId=l.userId2)" .
	" left join messages as m on (l.userId2=m.fromId and l.userId1=m.userId and m.bNew='Y')" .
	" where userId1=" . to_sql($userId, "Number") . " AND linkId=0" .
	" group by u.userId,u.login";*/
$contacts->m_sql = "select u.userId, u.login as login,count(m.messageId) as nMess, p.picId, floor((TO_DAYS(now())-TO_DAYS(u.birth))/365) as age, IF(u.sex='M', 'мужской', 'женский') AS sex" .
	" from ((links as l LEFT JOIN pics AS p ON l.userId2=p.userId AND p.bMain='Y') left join " . 
	"users as u on u.userId=l.userId2)" .
	" left join messages as m on (l.userId2=m.fromId and l.userId1=m.userId and m.bNew='Y')" .
	" where userId1=" . to_sql($userId, "Number") . " AND linkId=0 AND u.userId IS NOT NULL" .
	" group by u.userId,u.login";
$contacts->m_fields["userId"] = Array ("userId", null, "");
$contacts->m_fields["login"] = Array ("login", null, "");
$contacts->m_fields["nMess"] = Array ("nMess", null, "");
$contacts->m_fields["picId"] = Array ("picId", null, "");
$contacts->m_fields["age"] = Array ("age", null, "");
$contacts->m_fields["sex"] = Array ("sex", null, "");
$contacts->m_itemBlocks["bNew"] = 0;
$contacts->m_itemBlocks["bIncome"] = 0;
$contacts->m_sort = "nMess";
$contacts->m_dir = "desc";
$page->add($contacts);


$blacklist = new CHtmlGrid($db, "blacklist", null);
$blacklist->m_sqlcount = "select count(*) as cnt from links where userId1=" . to_sql($userId, "Number") . " AND linkId=1";
$blacklist->m_sql = "select u.userId, u.login as login, p.picId, floor((TO_DAYS(now())-TO_DAYS(u.birth))/365) as age, IF(u.sex='M', 'мужской', 'женский') AS sex" .
	" from (links as l LEFT JOIN pics AS p ON l.userId2=p.userId AND p.bMain='Y') left join users as u on u.userId=l.userId2" .
	" where userId1=" . to_sql($userId, "Number") . " AND linkId=1";
$blacklist->m_fields["userId"] = Array ("userId", null, "");
$blacklist->m_fields["login"] = Array ("login", null, "");
$blacklist->m_fields["picId"] = Array ("picId", null, "");
$blacklist->m_fields["age"] = Array ("age", null, "");
$blacklist->m_fields["sex"] = Array ("sex", null, "");
$blacklist->m_sort = "login";
$blacklist->m_dir = "asc";
$page->add($blacklist);



if (get_param("cmd", "") == "block")
{
	$userId = get_param("userId", 0);
	if ($userId > 0)
	{
		$db->execute("UPDATE links SET linkId=1 WHERE userId1=" . to_sql(get_session("_userId"), "Number") .
			" AND userId2=" . to_sql($userId, "Number"));
		$blacklist->sMessage = "Контакт добавлен в черный список";
	}
}
if (get_param("cmd", "") == "unblock")
{
	$userId = get_param("userId", 0);
	if ($userId > 0)
	{
		$db->execute("UPDATE links SET linkId=0 WHERE userId1=" . to_sql(get_session("_userId"), "Number") .
			" AND userId2=" . to_sql($userId, "Number"));
		$blacklist->sMessage = "Контакт убран из черного списка";
	}
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


if ($g_options["sendPrice"] > 0)
{
	$turbo = new CHtmlBlock("turbo", null);
	$page->add($turbo);
}


$page->init();
$page->action();
$page->parse(null);


?>
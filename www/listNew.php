<?php


include_once("include/core.php");
include_once("include/db.php");
include_once("include/html.php");
include_once("include/params.php");
include_once("include/block.php");
include_once("include/grid.php");
include_once("include/record.php");
include_once("include/common.php");
include_once("include/public.php");
include_once("include/home.php");


get_block_params("searchlist");


class CListGrid extends CHtmlGrid
{
	function init()
	{
		parent::init();

		$sWhere = " WHERE bActive='Y' AND registered > DATE_SUB(now(),INTERVAL 10 DAY)";

		$this->m_sqlcount = "SELECT COUNT(DISTINCT u.userId) as n, u.userId FROM (users AS u LEFT JOIN pics as p ON (p.userId=u.userId AND p.bMain='Y' AND p.bApproved='Y')) LEFT JOIN videos as v ON v.userId=u.userId" . $sWhere . " GROUP BY NULL";
		$this->m_sql = "SELECT DISTINCT u.bVIP, u.userId, login, floor((TO_DAYS(now())-TO_DAYS(birth))/365) as age, c.title as city, lookSex, sex, left(about, 32) as about, picId" .
			" FROM ((users AS u LEFT JOIN cities as c ON u.cityId=c.cityId) LEFT JOIN pics as p ON (p.userId=u.userId AND p.bMain='Y' AND p.bApproved='Y')) LEFT JOIN videos as v ON v.userId=u.userId" . $sWhere;


//echo "<hr>" . $this->m_sqlcount . "<hr>";
//echo "<hr>" . $this->m_sql . "<hr>";

	}


	function onItem()
	{
		global $g_sex;
		global $g_lookSex;

		if (strlen($this->m_fields["about"][2]) == 32)
			$this->m_fields["about"][2] .= "...";

		$this->m_fields["sex"][2] = $g_sex[$this->m_fields["sex"][2]];
		$this->m_fields["lookSex"][2] = $g_lookSex[$this->m_fields["lookSex"][2]];

		if ($this->m_fields["picId"][2] == "")
			$this->m_fields["img"][2] = "no.jpg";
		else	
			$this->m_fields["img"][2] = $this->m_fields["userId"][2] . "_" . $this->m_fields["picId"][2] . "_s.jpg";

		if ($this->m_fields["bVIP"][2] == "Y")
		{
			$this->m_itemBlocks["vip"] = 1;
			$this->m_itemBlocks["novip"] = 0;
		} else {
			$this->m_itemBlocks["vip"] = 0;
			$this->m_itemBlocks["novip"] = 1;
		}
	}	

}



$db = new CDB();
$db->connect();
loadOptions($db);

$page = new CCommonPage("", "html/" . $g_theme . "/listNew.html");
$page->add(new CCommonHeader($db, "iHeader", "html/" . $g_theme . "/header.html"));
$page->add(new CHtmlBlock("iFooter", "html/" . $g_theme . "/footer.html"));

$list = new CListGrid($db, "searchlist", "html/" . $g_theme . "/searchlist.html");
$list->m_fields["userId"] = Array ("userId", null, "");
$list->m_fields["login"] = Array ("login", null, "");
$list->m_fields["age"] = Array ("age", null, "");
$list->m_fields["sex"] = Array ("sex", null, "");
$list->m_fields["lookSex"] = Array ("lookSex", null, "");
$list->m_fields["city"] = Array ("city", null, "");
$list->m_fields["about"] = Array ("about", null, "");
$list->m_fields["picId"] = Array ("picId", null, "");
$list->m_fields["img"] = Array ("img", "no.jpg", "");
$list->m_fields["bVIP"] = Array ("bVIP", null, "");
$list->m_itemBlocks["vip"] = 0;
$list->m_itemBlocks["novip"] = 0;
$list->m_sort = "registered";
$list->m_dir = "desc";
$page->add($list);

$page->add(new CLider($db, "lider", null));

$searchForm = new CSimpleSearchForm($db, "search", "html/" . $g_theme . "/searchSimple.html");
$searchForm->m_withPic = 1;
$page->add($searchForm);

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
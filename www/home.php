<?php


include_once("include/core.php");
include_once("include/db.php");
include_once("include/html.php");
include_once("include/params.php");
include_once("include/block.php");
include_once("include/grid.php");
include_once("include/common.php");
include_once("include/public.php");
include_once("include/home.php");

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

$db = new CDB();
$db->connect();
loadOptions($db);

$page = new CLoggedPage("", "html/" . $g_theme . "/home.html");
$page->add(new CCommonHeader($db, "iHeader", "html/" . $g_theme . "/header.html"));
$page->add(new CHtmlBlock("iFooter", "html/" . $g_theme . "/footer.html"));
$searchForm = new CSimpleSearchForm($db, "search", "html/" . $g_theme . "/searchSimple.html");
$searchForm->m_withPic = 1;
$page->add($searchForm);

$page->add(new CLider($db, "lider", null));

if ($g_options["new10"] > 0)
{
	$new10 = new CNew10Grid($db, "new10", null);
	$new10->m_fields["userId"] = Array ("userId", null, "");
	$new10->m_fields["login"] = Array ("login", null, "");
	$new10->m_fields["age"] = Array ("age", null, "");
	$new10->m_fields["sex"] = Array ("sex", null, "");
	$new10->m_fields["lookSex"] = Array ("lookSex", null, "");
	$new10->m_fields["city"] = Array ("city", null, "");
	$new10->m_fields["about"] = Array ("about", null, "");
	$new10->m_fields["picId"] = Array ("picId", null, "");
	$new10->m_fields["img"] = Array ("img", "no.jpg", "");
	$new10->m_fields["bVIP"] = Array ("bVIP", null, "");
	$new10->m_itemBlocks["vip"] = 0;
	$new10->m_itemBlocks["novip"] = 0;
	$page->add($new10);
}
if ($g_options["top10"] > 0)
{
	$top10 = new CTop10Grid($db, "top10", null);
	$top10->m_fields["userId"] = Array ("userId", null, "");
	$top10->m_fields["login"] = Array ("login", null, "");
	$top10->m_fields["age"] = Array ("age", null, "");
	$top10->m_fields["sex"] = Array ("sex", null, "");
	$top10->m_fields["lookSex"] = Array ("lookSex", null, "");
	$top10->m_fields["city"] = Array ("city", null, "");
	$top10->m_fields["about"] = Array ("about", null, "");
	$top10->m_fields["picId"] = Array ("picId", null, "");
	$top10->m_fields["img"] = Array ("img", "no.jpg", "");
	$top10->m_fields["bVIP"] = Array ("bVIP", null, "");
	$top10->m_itemBlocks["vip"] = 0;
	$top10->m_itemBlocks["novip"] = 0;
	$page->add($top10);
}
if ($g_options["view10"] > 0)
{
	$view10 = new CView10Grid($db, "view10", null);
	$view10->m_fields["userId"] = Array ("userId", null, "");
	$view10->m_fields["login"] = Array ("login", null, "");
	$view10->m_fields["age"] = Array ("age", null, "");
	$view10->m_fields["sex"] = Array ("sex", null, "");
	$view10->m_fields["lookSex"] = Array ("lookSex", null, "");
	$view10->m_fields["city"] = Array ("city", null, "");
	$view10->m_fields["about"] = Array ("about", null, "");
	$view10->m_fields["picId"] = Array ("picId", null, "");
	$view10->m_fields["img"] = Array ("img", "no.jpg", "");
	$view10->m_fields["bVIP"] = Array ("bVIP", null, "");
	$view10->m_itemBlocks["vip"] = 0;
	$view10->m_itemBlocks["novip"] = 0;
	$page->add($view10);
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



if ($g_options["bannerUrl"] != "")
{
	$banner = new CBannerView($db, "bannerView", null);
	$page->add($banner);
}


$page->init();
$page->action();
$page->parse(null);





?>
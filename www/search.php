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


class CSearchGrid extends CHtmlGrid
{
	function init()
	{
		parent::init();

		$sWhere = " WHERE bActive='Y'";

		$searchForm = isset($this->m_parent->m_blocks["search"]) ? $this->m_parent->m_blocks["search"] : null;
		

		$ageFrom = "";
		$ageTo = "";
		$sex = "";
		$lookSex = "";
		$cityId = "";

		$hightFrom = "";
		$hightTo = "";
		$weightFrom = "";
		$weightTo = "";
		$moneyId = "";
		$childrenId = "-1";
		$bMarried = "";
		$homeId = "";
		$alcoholId = "";
		$smokeId = "";
		$goalIds = "";
		$interesIds = "";
		$langIds = "";

		$withPic = "";
		$withVideo = "";

		if ($searchForm != null)
		{
			if (isset($searchForm->m_ageFrom))
				$ageFrom = $searchForm->m_ageFrom;
			if (isset($searchForm->m_ageTo))
				$ageTo = $searchForm->m_ageTo;
			if (isset($searchForm->m_sex))
				$sex = $searchForm->m_sex;
			if (isset($searchForm->m_lookSex))
				$lookSex = $searchForm->m_lookSex;
			if (isset($searchForm->m_cityId))
				$cityId = $searchForm->m_cityId;

			if (isset($searchForm->m_hightFrom))
				$hightFrom = $searchForm->m_hightFrom;
			if (isset($searchForm->m_hightTo))
				$hightTo = $searchForm->m_hightTo;
			if (isset($searchForm->m_weightFrom))
				$weightFrom = $searchForm->m_weightFrom;
			if (isset($searchForm->m_weightTo))
				$weightTo = $searchForm->m_weightTo;
			if (isset($searchForm->m_moneyId))
				$moneyId = $searchForm->m_moneyId;
			if (isset($searchForm->m_childrenId))
				$childrenId = $searchForm->m_childrenId;
			if (isset($searchForm->m_bMarried))
				$bMarried = $searchForm->m_bMarried;
			if (isset($searchForm->m_homeId))
				$homeId = $searchForm->m_homeId;
			if (isset($searchForm->m_alcoholId))
				$alcoholId = $searchForm->m_alcoholId;
			if (isset($searchForm->m_smokeId))
				$smokeId = $searchForm->m_smokeId;
			if (isset($searchForm->m_goalIds))
				$goalIds = $searchForm->m_goalIds;
			if (isset($searchForm->m_interesIds))
				$interesIds = $searchForm->m_interesIds;
			if (isset($searchForm->m_langIds))
				$langIds = $searchForm->m_langIds;

			if (isset($searchForm->m_withPic))
				$withPic = $searchForm->m_withPic;
			if (isset($searchForm->m_withVideo))
				$withVideo = $searchForm->m_withVideo;
		}

		$ageFrom = get_param("ageFrom", $ageFrom);
		if ($ageFrom != "")
			$sWhere .= " AND floor((TO_DAYS(now())-TO_DAYS(birth))/365)>=" . to_sql($ageFrom, "Number");

		$ageTo = get_param("ageTo", $ageTo);
		if ($ageTo != "")
			$sWhere .= " AND floor((TO_DAYS(now())-TO_DAYS(birth))/365)<=" . to_sql($ageTo, "Number");

		$sex = get_param("sex", $sex);
		if ($sex != "")
			$sWhere .= " AND sex=" . to_sql($sex, "");

		$lookSex = get_param("lookSex", $lookSex);
		if ($lookSex != "")
			$sWhere .= " AND lookSex=" . to_sql($lookSex, "");

		$cityId = get_param("cityId", $cityId);
		if ($cityId != "" && $cityId != 0)
			$sWhere .= " AND u.cityId=" . to_sql($cityId, "Number");


		$hightFrom = get_param("hightFrom", $hightFrom);
		if ($hightFrom != "")
			$sWhere .= " AND hight>=" . to_sql($hightFrom, "Number");

		$hightTo = get_param("hightTo", $hightTo);
		if ($hightTo != "")
			$sWhere .= " AND hight<=" . to_sql($hightTo, "Number");

		$weightFrom = get_param("weightFrom", $weightFrom);
		if ($weightFrom != "")
			$sWhere .= " AND weight>=" . to_sql($weightFrom, "Number");

		$weightTo = get_param("weightTo", $weightTo);
		if ($weightTo != "")
			$sWhere .= " AND weight<=" . to_sql($weightTo, "Number");

		$moneyId = get_param("moneyId", $moneyId);
		if ($moneyId != "" && $moneyId != 0)
			$sWhere .= " AND u.moneyId=" . to_sql($moneyId, "Number");

		$homeId = get_param("homeId", $homeId);
		if ($homeId != "" && $homeId != 0)
			$sWhere .= " AND u.homeId=" . to_sql($homeId, "Number");

		$alcoholId = get_param("alcoholId", $alcoholId);
		if ($alcoholId != "" && $alcoholId != 0)
			$sWhere .= " AND u.alcoholId=" . to_sql($alcoholId, "Number");

		$smokeId = get_param("smokeId", $smokeId);
		if ($smokeId != "" && $smokeId != 0)
			$sWhere .= " AND u.smokeId=" . to_sql($smokeId, "Number");

		$childrenId = get_param("childrenId", $childrenId);
		if ($childrenId != -1)
			$sWhere .= " AND u.childrenId=" . to_sql($childrenId, "Number");

		$bMarried = get_param("bMarried", $bMarried);
		if ($bMarried != "" && $bMarried != 0)
			$sWhere .= " AND u.bMarried=" . to_sql($childrenId, "");


		$goalIds = get_checks_param("goalIds", $goalIds);
		if ($goalIds != 0)
			$sWhere .= " AND (u.goalIds & " . to_sql($goalIds, "Number") . ")";

		$interesIds = get_checks_param("interesIds", $interesIds);
		if ($interesIds != 0)
			$sWhere .= " AND (u.interesIds & " . to_sql($interesIds, "Number") . ")";

		$withPic = get_checks_param("withPic", $withPic);
		if ($withPic != "")
			$sWhere .= " AND (p.picId is not null)";

		$withVideo = get_checks_param("withVideo", $withVideo);
		if ($withVideo != 0)
			$sWhere .= " AND (v.videoId is not null)";




		$this->m_sqlcount = "SELECT COUNT(DISTINCT u.userId) as n, u.userId FROM (users AS u LEFT JOIN pics as p ON (p.userId=u.userId AND p.bMain='Y' AND p.bApproved='Y')) LEFT JOIN videos as v ON v.userId=u.userId" . $sWhere . " GROUP BY NULL";
		$this->m_sql = "SELECT DISTINCT u.bVIP, u.userId, login, floor((TO_DAYS(now())-TO_DAYS(birth))/365) as age, c.title as city, lookSex, sex, left(about, 32) as about, picId" .
			" FROM ((users AS u LEFT JOIN cities as c ON u.cityId=c.cityId) LEFT JOIN pics as p ON (p.userId=u.userId AND p.bMain='Y' AND p.bApproved='Y')) LEFT JOIN videos as v ON v.userId=u.userId" . $sWhere;

/*
echo "<hr>" . $this->m_sqlcount . "<hr>";
echo "<hr>";
$this->m_db->query($this->m_sqlcount);
while ($row = $this->m_db->fetch_row())
{
	echo $row["n"] . "<br>";
}
$this->m_db->free_result();
echo "<hr>";
echo "<hr>" . $this->m_sql . "<hr>";
*/

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


$bVIP = 0;
if ($db->DLookUp("SELECT bVIP FROM users WHERE userId=" . to_sql(get_session("_userId"), "Number")) === "Y")
{
	$bVIP = 1;
}


if ($bVIP and !isset($_POST['cmd']))
{
	$page = new CCommonPage("", "html/" . $g_theme . "/searchForVip.html");
}
elseif ($bVIP and $_POST['cmd'] == "search")
{
	$page = new CCommonPage("", "html/" . $g_theme . "/search.html");
}
else
{
	$page = new CCommonPage("", "html/" . $g_theme . "/search.html");
}

$page->add(new CCommonHeader($db, "iHeader", "html/" . $g_theme . "/header.html"));
$page->add(new CHtmlBlock("iFooter", "html/" . $g_theme . "/footer.html"));

if ($bVIP and !isset($_POST['cmd']))
{
	$page->add(new CVIPSearchForm($db, "search", "html/" . $g_theme . "/searchVIP.html"));
	
	$searchlist = new CSearchGrid($db, "searchlist", "blank.html");
	$page->add($searchlist);
}
elseif ($bVIP and $_POST['cmd'] == "search")
{
	$page->add(new CSimpleSearchForm($db, "search", "html/" . $g_theme . "/searchSimple.html"));
	
	$searchlist = new CSearchGrid($db, "searchlist", "html/" . $g_theme . "/searchlist.html");
	$searchlist->m_fields["userId"] = Array ("userId", null, "");
	$searchlist->m_fields["login"] = Array ("login", null, "");
	$searchlist->m_fields["age"] = Array ("age", null, "");
	$searchlist->m_fields["sex"] = Array ("sex", null, "");
	$searchlist->m_fields["lookSex"] = Array ("lookSex", null, "");
	$searchlist->m_fields["city"] = Array ("city", null, "");
	$searchlist->m_fields["about"] = Array ("about", null, "");
	$searchlist->m_fields["picId"] = Array ("picId", null, "");
	$searchlist->m_fields["img"] = Array ("img", "no.jpg", "");
	$searchlist->m_fields["bVIP"] = Array ("bVIP", null, "");
	$searchlist->m_itemBlocks["vip"] = 0;
	$searchlist->m_itemBlocks["novip"] = 0;
	$page->add($searchlist);
}
else
{
	$page->add(new CSimpleSearchForm($db, "search", "html/" . $g_theme . "/searchSimple.html"));
	
	$searchlist = new CSearchGrid($db, "searchlist", "html/" . $g_theme . "/searchlist.html");
	$searchlist->m_fields["userId"] = Array ("userId", null, "");
	$searchlist->m_fields["login"] = Array ("login", null, "");
	$searchlist->m_fields["age"] = Array ("age", null, "");
	$searchlist->m_fields["sex"] = Array ("sex", null, "");
	$searchlist->m_fields["lookSex"] = Array ("lookSex", null, "");
	$searchlist->m_fields["city"] = Array ("city", null, "");
	$searchlist->m_fields["about"] = Array ("about", null, "");
	$searchlist->m_fields["picId"] = Array ("picId", null, "");
	$searchlist->m_fields["img"] = Array ("img", "no.jpg", "");
	$searchlist->m_fields["bVIP"] = Array ("bVIP", null, "");
	$searchlist->m_itemBlocks["vip"] = 0;
	$searchlist->m_itemBlocks["novip"] = 0;
	$page->add($searchlist);
}



$page->add(new CLider($db, "lider", null));

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
<?php


//
// these are for all pages of public area
//


// blocks params
$g_params = Array(
	"pSearch" => Array("searchOffset", "searchSort", "searchDir"),
	"pView" => Array("userId"),
	"searchlist" => Array ("ageFrom", "ageTo", "sex", "lookSex", "cityId", "searchlistOffset", "searchlistSort", "searchlistDir"),
	"pMail" => Array ("contactsOffset", "contactsSort", "contactsDir", "blacklistOffset", "blacklistSort", "blacklistDir"),
	"pCompose" => Array ("userId"),
);

// blocks dependencies
// page is a block
$g_depends = Array(
	"pView" => Array("pSearch"),
	"pCompose" => Array ("pMail"),
);



if (get_session("_sex") == "M")
	$g_theme = "M";
else if (get_session("_sex") == "F")
	$g_theme = "F";
else
	$g_theme = "public";


$g_images = "img/" . $g_theme;



if (get_session("_cityId") == "" && get_session("_geoip") == "")
{
	set_session("_geoip", "1");

	include("geoipcity.php");
	$gi = geoip_open(GEOIP_DIR . "GeoIPCity.dat", GEOIP_STANDARD);
	$ip = getenv("REMOTE_ADDR");
//	$ip = "85.249.192.105";
	$record = geoip_record_by_addr($gi, $ip);
	if ($record)
	{
		$db = new CDB();
		$db->connect();
		$sCity = $record->city;
		if ($sCity != "")
		{
			$cityId = $db->DLookUp("SELECT cityId FROM cities WHERE sGeoipCity=" . to_sql($sCity, ""));
			if ($cityId > 0)
				set_session("_cityId", $cityId);
		}
        $db->close();
	}
	geoip_close($gi);

//set_session("_cityId", 1);
}





// only public (not logged in)
class CPublicPage extends CHtmlBlock
{
	function CPublicPage($name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
	}

	function init()
	{
		if (get_session("_userId") != "")
		{
			header("Location: home.php?mes=login\n");
			exit;
		}

		parent::init();
	}

}


// only logged in
class CLoggedPage extends CHtmlBlock
{
	function CLoggedPage($name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
	}

	function init()
	{
		if (get_session("_userId") == "")
		{
			header("Location: login.php?mes=login\n");
			exit;
		}

		parent::init();
	}

}

// for pages maybe used in both modes
class CCommonPage extends CHtmlBlock
{
	function CCommonPage($name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
	}

	function init()
	{
		parent::init();
	}

}



class CCommonHeader extends CHtmlBlock
{
	var $m_db;
	function CCommonHeader($db, $name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
		$this->m_db = $db;
	}

	function parseBlock(&$html)
	{
		global $g_options;

		if (get_session("_userId") != "")
		{
			$html->setvar("login", get_session("_login"));
			$html->setvar("sex", get_session("_sex"));
			$html->setvar("city", get_session("_city"));
			$html->setvar("sex", get_session("_sex"));
			$html->setvar("age", get_session("_age"));
			
			if ($g_options["videoVIP"] != 1 || get_session("_bVIP") == "Y")
				$html->parse("bVideoMenu");
			if ($g_options["liderTypeId"] == "2")
			{
				$html->setvar("userId", get_session("_userId"));
				$html->parse("bLiderMenu");
			}

			$nMailNew = $this->m_db->DLookUp("SELECT count(*) FROM messages WHERE bNew='Y' AND userId=" . to_sql(get_session("_userId"), "Number"));

			if ($nMailNew > 0)
			{
				$html->setvar("nMailNew", $nMailNew);
				$html->parse("bMailNew");
			} else {
				$html->parse("bMail");
			}

			$nPresentsNew = $this->m_db->DLookUp("SELECT count(*) FROM sentPresents WHERE bNew='Y' AND userId2=" . to_sql(get_session("_userId"), "Number"));

			if ($nPresentsNew > 0)
			{
				$html->setvar("nPresents", $nPresentsNew);
				$html->parse("bPresentsNew");
			} else {
				$html->parse("bPresents");
			}

			// update lastAccess
			$this->m_db->execute("UPDATE users SET lastAccess=now() WHERE userId=" . to_sql(get_session("_userId"), "Number"));
		} else {
		}

		$html->setvar("nAllUsers", $this->m_db->DLookUp("SELECT COUNT(*) FROM users WHERE bActive='Y'"));
		$html->setvar("nNewUsers", $this->m_db->DLookUp("SELECT COUNT(*) FROM users WHERE bActive='Y' AND registered > DATE_SUB(now(),INTERVAL 10 DAY)"));
		$html->setvar("nOnlineUsers", $this->m_db->DLookUp("SELECT COUNT(*) FROM users WHERE bActive='Y' AND lastAccess > DATE_SUB(now(),INTERVAL 30 MINUTE)"));

		$html->parse("mainMenu");
		parent::parseBlock(&$html);
	}

}


class CSimpleSearchForm extends CHtmlBlock
{
	var $m_db;

	var $m_ageFrom = "";
	var $m_ageTo = "";
	var $m_sex = "";
	var $m_lookSex = "";
	var $m_cityId = "";
	var $m_withPic = "";
	var $m_withVideo = "";

	function CSimpleSearchForm($db, $name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
		$this->m_db = $db;

		if (get_session("_userId") != "")
		{
			$this->m_sex = get_session("_lookSex");
			$this->m_lookSex = get_session("_sex");
		}
		if (get_session("_cityId") != "")
		{
			$this->m_cityId = get_session("_cityId");
		}
		$this->m_ageFrom = get_param("ageFrom", $this->m_ageFrom);
		$this->m_ageTo = get_param("ageTo", $this->m_ageTo);
		$this->m_sex = get_param("sex", $this->m_sex);
		$this->m_lookSex = get_param("lookSex", $this->m_lookSex);
		$this->m_cityId = get_param("cityId", $this->m_cityId);
		$this->m_withPic = get_param("withPic", $this->m_withPic);
		$this->m_withVideo = get_param("withVideo", $this->m_withVideo);

	}

	function parseBlock(&$html)
	{
		global $g_sex;
		global $g_lookSex;
		$html->setvar("ageFrom", $this->m_ageFrom);
		$html->setvar("ageTo", $this->m_ageTo);
		$html->setvar("lookSexOptions", HSelectOptions($g_sex, $this->m_lookSex));
		$html->setvar("sexOptions", HSelectOptions($g_lookSex, $this->m_sex));
		$html->setvar("cityOptions", $this->m_db->DSelectOptions("SELECT cityId, title FROM cities ORDER BY priority,title", $this->m_cityId));
		if ($this->m_withPic)
			$html->setvar("withPic", " checked");
		if ($this->m_withVideo)
			$html->setvar("withVideo", " checked");

		parent::parseBlock(&$html);
	}

}


class CVIPSearchForm extends CHtmlBlock
{
	var $m_db;

	var $m_ageFrom = "";
	var $m_ageTo = "";
	var $m_sex = "";
	var $m_lookSex = "";
	var $m_cityId = "";

	var $m_hightFrom = "";
	var $m_hightTo = "";
	var $m_weightFrom = "";
	var $m_weightTo = "";
	var $m_moneyId = "";
	var $m_childrenId = "-1";
	var $m_bMarried = "";
	var $m_homeId = "";
	var $m_alcoholId = "";
	var $m_smokeId = "";

	var $m_goalIds = "";
	var $m_interesIds = "";
	var $m_langIds = "";

	var $m_withPic = "";
	var $m_withVideo = "";

	function CVIPSearchForm($db, $name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
		$this->m_db = $db;

		if (get_session("_userId") != "")
		{
			$this->m_sex = get_session("_lookSex");
			$this->m_lookSex = get_session("_sex");
		}
		if (get_session("_cityId") != "")
		{
			$this->m_cityId = get_session("_cityId");
		}
		$this->m_ageFrom = get_param("ageFrom", $this->m_ageFrom);
		$this->m_ageTo = get_param("ageTo", $this->m_ageTo);
		$this->m_sex = get_param("sex", $this->m_sex);
		$this->m_lookSex = get_param("lookSex", $this->m_lookSex);
		$this->m_cityId = get_param("cityId", $this->m_cityId);

		$this->m_hightFrom = get_param("hightFrom", $this->m_hightFrom);
		$this->m_hightTo = get_param("hightTo", $this->m_hightTo);
		$this->m_weightFrom = get_param("weightFrom", $this->m_weightFrom);
		$this->m_weightTo = get_param("weightTo", $this->m_weightTo);
		$this->m_moneyId = get_param("moneyId", $this->m_moneyId);
		$this->m_childrenId = get_param("childrenId", $this->m_childrenId);
		$this->m_bMarried = get_param("bMarried", $this->m_bMarried);
		$this->m_homeId = get_param("homeId", $this->m_homeId);
		$this->m_alcoholId = get_param("alcoholId", $this->m_alcoholId);
		$this->m_smokeId = get_param("smokeId", $this->m_smokeId);

		$this->m_goalIds = get_checks_param("goalIds");
		$this->m_interesIds = get_checks_param("interesIds");
		$this->m_langIds = get_checks_param("langIds");

		$this->m_withPic = get_param("withPic", $this->m_withPic);
		$this->m_withVideo = get_param("withVideo", $this->m_withVideo);
	}

	function parseBlock(&$html)
	{
		global $g_sex;
		global $g_lookSex;
		global $g_children;
		global $g_married;

		$html->setvar("ageFrom", $this->m_ageFrom);
		$html->setvar("ageTo", $this->m_ageTo);
		$html->setvar("lookSexOptions", HSelectOptions($g_sex, $this->m_lookSex));
		$html->setvar("sexOptions", HSelectOptions($g_lookSex, $this->m_sex));
		$html->setvar("cityOptions", $this->m_db->DSelectOptions("SELECT cityId, title FROM cities ORDER BY priority,title", $this->m_cityId));

		$html->setvar("hightFrom", $this->m_hightFrom);
		$html->setvar("hightTo", $this->m_hightTo);
		$html->setvar("weightFrom", $this->m_weightFrom);
		$html->setvar("weightTo", $this->m_weightTo);
		$html->setvar("moneyOptions", $this->m_db->DSelectOptions("SELECT moneyId, title FROM money", $this->m_moneyId));
		$html->setvar("childrenOptions", HSelectOptions($g_children, $this->m_childrenId));
		$html->setvar("homeOptions", $this->m_db->DSelectOptions("SELECT homeId, title FROM homes", $this->m_homeId));
		$html->setvar("alcoholOptions", $this->m_db->DSelectOptions("SELECT alcoholId, title FROM alcohols", $this->m_alcoholId));
		$html->setvar("smokeOptions", $this->m_db->DSelectOptions("SELECT smokeId, title FROM smokes", $this->m_smokeId));

		$html->setvar("mariedOptions", HSelectOptions($g_married, $this->m_bMarried));

		rec_parse_checks("goalIds", $this->m_db, &$html, "SELECT maskId,title FROM goals", $this->m_goalIds);
		rec_parse_checks("interesIds", $this->m_db, &$html, "SELECT maskId,title FROM intereses", $this->m_interesIds);
		rec_parse_checks("langIds", $this->m_db, &$html, "SELECT maskId,title FROM langs", $this->m_langIds);

		if ($this->m_withPic)
			$html->setvar("withPic", " checked");
		if ($this->m_withVideo)
			$html->setvar("withVideo", " checked");
	
		parent::parseBlock(&$html);
	}

}


class CBannerView extends CHtmlBlock
{
	var $m_db;

	function CBannerView($db, $name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
		$this->m_db = $db;
	}

	function parseBlock(&$html)
	{
		global $g_options;
		
		if ($g_options["bannerUrl"] != "")
		{
			$html->setvar("bannerId", $g_options["bannerId"]);
			$html->setvar("bannerUrl", $g_options["bannerUrl"]);
			parent::parseBlock(&$html);
		}

	}

}


?>
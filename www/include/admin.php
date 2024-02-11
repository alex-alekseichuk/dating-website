<?php


//
// these are for all pages of admin area
//

$g_images = "../img/admin";


// blocks params
$g_params = Array(
	"users" => Array("usersOffset", "usersSort", "usersDir"),
	"user" => Array("userId"),
	"cities" => Array("citiesOffset", "citiesSort", "citiesDir"),
	"city" => Array("cityId"),
	"goals" => Array("goalsOffset", "goalsSort", "goalsDir"),
	"goal" => Array("goalId"),
	"moneys" => Array("moneysOffset", "moneysSort", "moneysDir"),
	"money" => Array("moneyId"),
	"homes" => Array("homesOffset", "homesSort", "homesDir"),
	"home" => Array("homeId"),
	"intereses" => Array("interesesOffset", "interesesSort", "interesesDir"),
	"interes" => Array("interesId"),
	"langs" => Array("langsOffset", "langsSort", "langsDir"),
	"lang" => Array("langId"),
	"alcohols" => Array("alcoholsOffset", "alcoholsSort", "alcoholsDir"),
	"alcohol" => Array("alcoholId"),
	"smokes" => Array("smokesOffset", "smokesSort", "smokesDir"),
	"smoke" => Array("smokeId"),
	"presents" => Array("presentsOffset", "presentsSort", "presentsDir"),
	"present" => Array("presentId"),
	"votes" => Array("votesOffset", "votesSort", "votesDir"),
	"vote" => Array("voteId")
);

// blocks dependencies
// page is a block
$g_depends = Array(
	"user" => Array("users"),
	"city" => Array("cities"),
	"goal" => Array("goals"),
	"money" => Array("moneys"),
	"home" => Array("homes"),
	"interes" => Array("intereses"),
	"lang" => Array("langs"),
	"alcohol" => Array("alcohols"),
	"smoke" => Array("smokes"),
	"present" => Array("presents"),
	"vote" => Array("votes")
);



class CAdminPage extends CHtmlBlock
{
	function CAdminPage($name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
	}

	function init()
	{
		if (get_session("_admin") != "admin")
		{
			header("Location: login.php?mes=login\n");
			exit;
		}

		parent::init();
	}

}

class CAdminHeader extends CHtmlBlock
{
	function CAdminHeader($name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
	}

	function parseBlock(&$html)
	{
		if (get_session("_admin") == "admin")
		{
			$html->parse("mainMenu");
		}
		parent::parseBlock(&$html);
	}

}

?>
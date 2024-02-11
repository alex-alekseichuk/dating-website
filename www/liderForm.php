<?php


include_once("include/core.php");
include_once("include/db.php");
include_once("include/html.php");
include_once("include/params.php");
include_once("include/block.php");
include_once("include/grid.php");
include_once("include/common.php");
include_once("include/public.php");

$db = new CDB();
$db->connect();
loadOptions($db);

class CLiderFormPage extends CLoggedPage
{
	function parseBlock(&$html)
	{
		global $g_options;
		global $db;

		$userId = get_session("_userId");
		$points100 = $db->DLookUp("SELECT points100 FROM cities, users WHERE cities.cityId=users.cityId AND userId=" . to_sql($userId, "Number"));
		if ($points100 <= 0)
			$points100 = $g_options["points100"];

		$html->setvar("points100", $points100);
		$html->setvar("gameTimeout", $g_options["gameTimeout"]);
		$html->setvar("gamePrice", $g_options["gamePrice"]);
		$html->setvar("MAX_HELLO", MAX_HELLO);

		parent::parseBlock(&$html);
	}
}



$page = new CLiderFormPage("", "html/" . $g_theme . "/liderForm.html");

$page->init();
$page->action();
$page->parse(null);


?>
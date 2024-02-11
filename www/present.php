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


$db = new CDB();
$db->connect();
loadOptions($db);


$userId = get_param("userId", 0);

if ($userId == 0)
{
	header("Location: home.php\n");
	exit;
}



class CPresents extends CHtmlBlock
{
	var $m_db = null;
	var $sMessage = "";

	function CPresents($db, $name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
		$this->m_db = $db;
	}

	function action()
	{
		global $userId;
		$meId = get_session("_userId");

		$cmd = get_param("cmd", "");
		if ($cmd == "send")
		{
			$presentId = get_param("presentId", "");
			$message = get_param("message", "");
			if ($presentId > 0)
			{
				$this->m_db->execute("INSERT INTO sentPresents (userId1, userId2, presentId, message, sent) VALUES (" .
					to_sql($meId, "Number") . "," .
					to_sql($userId, "Number") . "," .
					to_sql($presentId, "Number") . "," .
					to_sql($message, "") . "," .
					"now())");
				$this->sMessage = "Подарок отослан.";
			} else {
				$this->sMessage = "Укажите подарок.";
			}
		}
	}	


	function parseBlock(&$html)
	{
		global $g_options;
		global $userId;
		$meId = get_session("_userId");

		$this->m_db->query("SELECT presentId FROM presents ORDER BY priority");
		while ($row = $this->m_db->fetch_row())
		{
			$html->setvar("presentId", $row["presentId"]);
			$html->parse("present", true);
		}

		$html->setvar("userId", $userId);
		$html->setvar("login", $this->m_db->DLookUp("SELECT login FROM users WHERE userId=" . to_sql($userId, "Number")));

		if ($this->sMessage != "")
		{
			$html->setvar("sMessage", $this->sMessage);
			$html->parsesafe("bMessage");
		}

		parent::parseBlock(&$html);
	}


}





$page = new CLoggedPage("", "html/" . $g_theme . "/present.html");
$page->add(new CCommonHeader($db, "iHeader", "html/" . $g_theme . "/header.html"));
$page->add(new CHtmlBlock("iFooter", "html/" . $g_theme . "/footer.html"));



$presents = new CPresents($db, "presents", null);
$page->add($presents);


$page->init();
$page->action();
$page->parse(null);



?>
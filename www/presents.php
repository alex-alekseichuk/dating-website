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
		$cmd = get_param("cmd", "");
		if ($cmd == "delete")
		{
			$userId = get_session("_userId");
			$sentPresentId = get_param("sentPresentId", "");
			if ($sentPresentId > 0 && $userId == $this->m_db->DLookUp("SELECT userId2 FROM sentPresents WHERE sentPresentId=" . to_sql($sentPresentId, "Number")))
			{
				$this->m_db->execute("DELETE FROM sentPresents WHERE sentPresentId=" . to_sql($sentPresentId, "Number"));
				$this->sMessage = "Подарок удален.";
			}
		}
	}	


	function parseBlock(&$html)
	{
		global $g_options;
		$userId = get_session("_userId");

		$this->m_db->execute("UPDATE sentPresents SET bNew='N' WHERE userId2=" . to_sql($userId, "Number"));

		$this->m_db->query("SELECT p.sentPresentId,u.userId,u.login,p.presentId,p.message,p.sent FROM sentPresents AS p JOIN users AS u ON u.userId=p.userId1 WHERE userId2=" . to_sql($userId, "Number") . " ORDER BY p.sentPresentId DESC");
		while ($row = $this->m_db->fetch_row())
		{
			$html->setvar("sentPresentId", $row["sentPresentId"]);
			$html->setvar("login", $row["login"]);
			$html->setvar("userId", $row["userId"]);
			$html->setvar("presentId", $row["presentId"]);
			$html->setvar("message", to_html($row["message"]));
			$html->setvar("sent", $row["sent"]);

			$html->parse("present", true);
		}

		if ($this->sMessage != "")
		{
			$html->setvar("sMessage", $this->sMessage);
			$html->parsesafe("bMessage");
		}

		parent::parseBlock(&$html);
	}


}





$page = new CLoggedPage("", "html/" . $g_theme . "/presents.html");
$page->add(new CCommonHeader($db, "iHeader", "html/" . $g_theme . "/header.html"));
$page->add(new CHtmlBlock("iFooter", "html/" . $g_theme . "/footer.html"));



$presents = new CPresents($db, "presents", null);
$page->add($presents);


$page->init();
$page->action();
$page->parse(null);



?>
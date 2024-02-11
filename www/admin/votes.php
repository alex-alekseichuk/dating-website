<?php


include_once("../include/core.php");
include_once("../include/db.php");
include_once("../include/html.php");
include_once("../include/params.php");
include_once("../include/block.php");
include_once("../include/grid.php");
include_once("../include/common.php");
include_once("../include/admin.php");

get_block_params("votes");


$db = new CDB();
$db->connect();
loadOptions($db);


class CQuestionForm extends CHtmlBlock
{
	var $m_db = null;
	var $sMessage = "";

	function CQuestionForm($db, $name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
		$this->m_db = $db;
	}

	function action()
	{
		$cmd = get_param("cmd", "");
		if ($cmd == "update")
		{
			$voting = get_param("voting", "");
			$this->m_db->execute("UPDATE options SET voting=" . to_sql($voting, ""));
			$this->sMessage = "Изменения приняты";
		}
	}	


	function parseBlock(&$html)
	{
		global $g_options;
		$html->setvar("voting", $g_options["voting"]);

		if ($this->sMessage != "")
		{
			$html->setvar("sMessage", $this->sMessage);
			if ($html->blockexists($this->m_name . "_bMessage"))
				$html->parse($this->m_name . "_bMessage");
		}

		parent::parseBlock(&$html);
	}


}


$page = new CAdminPage("", "../html/admin/votes.html");
$page->add(new CAdminHeader("iHeader", "../html/admin/header.html"));
$page->add(new CHtmlBlock("iFooter", "../html/admin/footer.html"));

$vote = new CQuestionForm($db, "vote", null);
$page->add($vote);


$items = new CHtmlGrid($db, "votes", null);
$items->m_sqlcount = "select count(*) as cnt from votes";
$items->m_sql = "select voteId,title,cnt,priority from votes";
$items->m_fields["voteId"] = Array ("voteId", null, "");
$items->m_fields["link"] = Array ("link", "vote.php?voteId=", "");
$items->m_fields["title"] = Array ("title", null, "");
$items->m_fields["cnt"] = Array ("cnt", null, "");
$items->m_fields["priority"] = Array ("priority", null, "");
$page->add($items);


$page->init();
$page->action();
$page->parse(null);



?>
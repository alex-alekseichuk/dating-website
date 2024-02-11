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



class CVoteForm extends CHtmlBlock
{
	var $m_db = null;
	var $sMessage = "";

	function CVoteForm($db, $name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
		$this->m_db = $db;
	}

	function action()
	{
		$cmd = get_param("cmd", "");
		if ($cmd == "vote")
		{
			$voteId = get_param("voteId", "");
			if ($voteId > 0)
			{
				$this->m_db->execute("UPDATE votes SET cnt=cnt+1 WHERE voteId=" . to_sql($voteId, "Number"));
				$this->sMessage = "Ваш голос принят.";
			}
		}
	}	


	function parseBlock(&$html)
	{
		global $g_options;
		$html->setvar("question", $g_options["voting"]);

		$total = $this->m_db->DLookUp("SELECT sum(cnt) FROM votes");

		$this->m_db->query("SELECT title, voteId, cnt FROM votes ORDER BY priority");
		$n = 0;
		while ($row = $this->m_db->fetch_row())
		{
			$n = $n + 1;
			$html->setvar("n", $n);
			$html->setvar("voteId", $row["voteId"]);
			$html->setvar("vote", $row["title"]);
			$html->setvar("cnt", $row["cnt"]);
			$percent = ceil($row["cnt"] * 100 / $total);
			if ($percent > 100) $percent = 100;
			if ($percent < 0) $percent = 0;
			$html->setvar("percent", $percent);
			$html->parse("vote", true);
		}


		if ($this->sMessage != "")
		{
			$html->setvar("sMessage", $this->sMessage);
			$html->parsesafe("bMessage");
		}

		parent::parseBlock(&$html);
	}


}






$page = new CPublicPage("", "html/public/vote.html");
$page->add(new CCommonHeader($db, "iHeader", "html/public/header.html"));
$page->add(new CHtmlBlock("iFooter", "html/public/footer.html"));


$vote = new CVoteForm($db, "voting", null);
$page->add($vote);


$page->init();
$page->action();
$page->parse(null);



?>
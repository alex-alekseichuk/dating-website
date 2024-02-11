<?php


include_once("include/core.php");
include_once("include/db.php");
include_once("include/html.php");
include_once("include/params.php");
include_once("include/block.php");
include_once("include/image.php");
include_once("include/grid.php");
include_once("include/common.php");
include_once("include/public.php");
include_once("include/record.php");

//get_block_params("pCompose");

$db = new CDB();
$db->connect();
loadOptions($db);





class CTurbo extends CHtmlBlock
{
	var $m_db = null;
	var $sMessage = "";

	function CTurbo($db, $name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
		$this->m_db = $db;
	}

	function action()
	{
		global $g_options;

		$cmd = get_param("cmd", "");
		if ($cmd == "turbo")
		{
			$userId = get_session("_userId");

			if ($g_options["sendPrice"] > $this->m_db->DLookUp("SELECT account FROM users WHERE userId=" . to_sql($userId, "Number")))
			{
				$this->sMessage = "У вас недостаточно денег на счету.";
				return;
			} else {
				$this->m_db->execute("UPDATE users SET account=account-" . to_sql($g_options["sendPrice"], "Number") . " WHERE userId=" . to_sql($userId, "Number"));
			}

			$message = get_param("message", "");
			$sex = get_param("sex", "");

			$cnt = 0;
			$Where = "";

			if ($sex != "")
				$Where = " WHERE u.sex=" . to_sql($sex, "");

			$this->m_db->query("SELECT DISTINCT u.userId FROM users AS u JOIN links AS l ON (l.linkId=0 AND l.userId1=u.userId AND l.userId2=" . to_sql($userId, "Number") . ")" . $Where);
			while ($row = $this->m_db->fetch_row())
			{
				if ($userId != $row["userId"])
				{
					$this->m_db->execute("INSERT INTO messages (fromId, userId, sent, message) VALUES (" .
						to_sql($userId, "Number") . "," .
						to_sql($row["userId"], "Number") . "," .
						"now()," .
						to_sql($message, "") .
						")");

					$cnt = $cnt + 1;
				}
			}

			if ($Where == "")
				$Where .= " WHERE ";
			else
				$Where .= " AND ";
			$Where .= "linkId IS NULL";

			$this->m_db->query("SELECT DISTINCT u.userId FROM users AS u LEFT JOIN links AS l ON (l.userId1=u.userId AND l.userId2=" . to_sql($userId, "Number") . ")" . $Where);
			while ($row = $this->m_db->fetch_row())
			{
				if ($userId != $row["userId"])
				{
					$this->m_db->execute("INSERT INTO messages (fromId, userId, sent, message) VALUES (" .
						to_sql($userId, "Number") . "," .
						to_sql($row["userId"], "Number") . "," .
						"now()," .
						to_sql($message, "") .
						")");
					$this->m_db->execute("INSERT INTO links (userId1, userId2, linkId) VALUES (" .
						to_sql($row["userId"], "Number") . "," .
						to_sql($userId, "Number") . "," .
						"0)");

					$cnt = $cnt + 1;
				}
			}

			$this->sMessage = "Рассылка разослана. Адресатов: " . $cnt;
		}
	}

	function parseBlock(&$html)
	{
		global $g_options;

		$userId = get_session("_userId");
		$html->setvar("account", $this->m_db->DLookUp("SELECT account FROM users WHERE userId=" . to_sql($userId, "Number")));
		$html->setvar("sendPrice", $g_options["sendPrice"]);

		if ($this->sMessage != "")
		{
			$html->setvar("sMessage", $this->sMessage);
			if ($html->blockexists("bMessage"))
				$html->parse("bMessage");
		}

		parent::parseBlock(&$html);
	}


}


$page = new CLoggedPage("", "html/" . $g_theme . "/turbo.html");
$page->add(new CCommonHeader($db, "iHeader", "html/" . $g_theme . "/header.html"));
$page->add(new CHtmlBlock("iFooter", "html/" . $g_theme . "/footer.html"));


$turbo = new CTurbo($db, "turbo", null);
$page->add($turbo);


$page->init();
$page->action();
$page->parse(null);


?>
<?php


include_once("include/core.php");
include_once("include/db.php");
include_once("include/html.php");
include_once("include/params.php");
include_once("include/block.php");
include_once("include/image.php");
include_once("include/grid.php");
include_once("include/record.php");
include_once("include/common.php");
include_once("include/public.php");

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

class CPhotoForm extends CHtmlBlock
{
	var $m_db = null;
	var $sMessage = "";

	function CPhotoForm($db, $name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
		$this->m_db = $db;
	}


	function action()
	{
		global $g_options;

		$cmd = get_param("cmd", "");
		$userId = get_session("_userId");

		if ($cmd == "update")
		{
			$picId = get_param("picId", 0);
			if ($picId == 0)
				return;

			$this->sMessage = validateFoto(0);
			if ($this->sMessage != "")
				return;

			uploadFoto($this->m_db, $userId, $picId, get_param("bMain", 0));
		}
		if ($cmd == "delete")
		{

			$picId = get_param("picId", 0);
			if ($picId == 0)
				return;
			
			if ($g_options["pic"] == 1)
			{
				if ($this->m_db->DLookUP("SELECT count(*) FROM pics WHERE userId=" . to_sql($userId, "Number")) <= 1)
				{
					$this->sMessage = "У вас должно быть хотя бы одно фото";
					return;
				}
			}

			deleteFoto($this->m_db, $userId, $picId);
			$this->sMessage = "Фото удалено";
		}
		if ($cmd == "main")
		{
			$picId = get_param("picId", 0);
			if ($picId == 0)
				return;

			if ($this->m_db->DLookUp("SELECT bApproved FROM pics WHERE userId=" . to_sql($userId, "Number") . " AND picId=" . to_sql($picId, "Number")) == "N")
				$this->sMessage = "Нельзя сделать заблокированное фото основным";

			$this->m_db->execute("UPDATE pics SET bMain='N' WHERE userId=" . to_sql($userId, "Number"));
			$this->m_db->execute("UPDATE pics SET bMain='Y' WHERE userId=" . to_sql($userId, "Number") . " AND picId=" . to_sql($picId, "Number"));
		}
		if ($cmd == "insert")
		{
			$this->sMessage = validateFoto(0);
			if ($this->sMessage != "")
				return;

			$bMain = get_param("bMain", 0);
			if ($bMain == 0)
				if ($this->m_db->DLookUP("SELECT count(*) FROM pics WHERE bMain='Y' AND userId=" . to_sql($userId, "Number")) <= 0)
					$bMain = 1;
			uploadFoto($this->m_db, $userId, "", $bMain);
		}
	}



	function parseBlock(&$html)
	{
		global $g_options;

		$userId = get_session("_userId");
		$html->setvar("userId", $userId);
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
				$html->parse("photo", true);
				$n++;
			}
			$this->m_db->free_result();
		}

		if ($n < $g_options["picNum"])
			$html->parse("nophoto");

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

$page = new CLoggedPage("", "html/" . $g_theme . "/photo.html");
$page->add(new CCommonHeader($db, "iHeader", "html/" . $g_theme . "/header.html"));
$page->add(new CHtmlBlock("iFooter", "html/" . $g_theme . "/footer.html"));


$photo_top = new CPhotoTop($db, "fPhotoTop", null);
$page->add($photo_top);

$photo = new CPhotoForm($db, "fPhoto", null);
$page->add($photo);

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
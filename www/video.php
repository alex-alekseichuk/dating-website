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


if ($g_options["videoVIP"] == 1 && get_session("_bVIP") != "Y")
{
	header("Location: home.php\n");
	exit;
}


class CVideoForm extends CHtmlBlock
{
	var $m_db = null;
	var $sMessage = "";

	function CVideoForm($db, $name, $html_path)
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
			$videoId = get_param("videoId", 0);
			if ($videoId == 0)
				return;

			$this->sMessage = validateVideo();
			if ($this->sMessage != "")
				return;

			uploadVideo($this->m_db, $userId, $videoId);
		}
		if ($cmd == "delete")
		{

			$videoId = get_param("videoId", 0);
			if ($videoId == 0)
				return;
			
			deleteVideo($this->m_db, $userId, $videoId);
			$this->sMessage = "Видео удалено";
		}
		if ($cmd == "insert")
		{
			$this->sMessage = validateVideo();
			if ($this->sMessage != "")
				return;

			uploadVideo($this->m_db, $userId, "");
		}
	}



	function parseBlock(&$html)
	{
		global $g_options;

		$userId = get_session("_userId");
		$html->setvar("userId", $userId);
		$html->setvar("MAX_FILE_SIZE", $g_options["videoSize"] * 1024 * 1024);
		
		$n = 0;
		if ($this->m_db->query("SELECT videoId,video FROM videos WHERE userId=" . to_sql($userId, "Number")))
		{
			while($row = $this->m_db->fetch_row())
			{
				$html->setvar("n", $n+1);
				$html->setvar("videoId", $row["videoId"]);
				$html->setvar("video", $row["video"]);
				$html->parse("video_", true);
				$n++;
			}
			$this->m_db->free_result();
		}

		if ($n < $g_options["videoNum"])
			$html->parse("novideo");

		if ($this->sMessage != "")
		{
			$html->setvar("sMessage", $this->sMessage);
			if ($html->blockexists($this->m_name . "_bMessage"))
				$html->parse($this->m_name . "_bMessage");
		}

		parent::parseBlock(&$html);
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

$page = new CLoggedPage("", "html/" . $g_theme . "/video.html");
$page->add(new CCommonHeader($db, "iHeader", "html/" . $g_theme . "/header.html"));
$page->add(new CHtmlBlock("iFooter", "html/" . $g_theme . "/footer.html"));

$video = new CVideoForm($db, "fVideo", null);
$page->add($video);

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
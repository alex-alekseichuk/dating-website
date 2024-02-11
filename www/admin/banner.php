<?php


include_once("../include/core.php");
include_once("../include/db.php");
include_once("../include/html.php");
include_once("../include/params.php");
include_once("../include/block.php");
include_once("../include/image.php");
include_once("../include/grid.php");
include_once("../include/record.php");
include_once("../include/common.php");
include_once("../include/admin.php");

//get_block_params("present");


$db = new CDB();
$db->connect();
loadOptions($db);



class CBannerForm extends CHtmlBlock
{
	var $m_db = null;
	var $sMessage = "";

	function CBannerForm($db, $name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
		$this->m_db = $db;
	}

	function action()
	{
		$cmd = get_param("cmd", "");
		if ($cmd == "update")
		{
			$this->sMessage = $this->customValidate();
			if ($this->sMessage == "")
			{
				$bannerId = $this->customAction();
				$url = get_param("url", "");
				if ($bannerId > 0)
					$this->m_db->execute("UPDATE options SET bannerUrl=" . to_sql($url, "") . ",bannerId=" . to_sql($bannerId, "Number"));
				else
					$this->m_db->execute("UPDATE options SET bannerUrl=" . to_sql($url, ""));
				$this->sMessage = "Изменения приняты";
			}
		}
	}	

	function customValidate()
	{
		global $HTTP_POST_FILES;

			$name = "picture";
			$ret = "";
			$exts = Array("gif", "jpg", "jpeg", "png");
			if (isset($HTTP_POST_FILES[$name]) && is_uploaded_file($HTTP_POST_FILES[$name]["tmp_name"]))
			{
				$sP = "";
				foreach ($exts as $ext)
				{
					if ($sP != "") $sP .= "|";
					$sP .= "(\." . $ext . ")";
				}
				$sP = "/(" . $sP . ")$/i";

				if (preg_match($sP, $HTTP_POST_FILES[$name]['name']) != 1)
				{
					return "Тип файла некорректный";
				}
			}
		return "";
	}

	function customAction()
	{
		global $HTTP_POST_FILES;

		$bannerId = "0";

			$name = "picture";
			if (isset($HTTP_POST_FILES[$name]) && is_uploaded_file($HTTP_POST_FILES[$name]["tmp_name"]))
			{
				$bannerId = abs(crc32(md5(uniqid(rand()))));
				@unlink(IMG_DIR . "banner_" . $this->m_db->DLookUp("SELECT bannerId FROM options") . ".jpg");
				@move_uploaded_file($HTTP_POST_FILES[$name]['tmp_name'],
					IMG_DIR . "banner_" . $bannerId . ".jpg");
			}

		return $bannerId;
	}

	function parseBlock(&$html)
	{
		$html->setvar("url", $this->m_db->DLookUp("SELECT bannerUrl FROM options"));
		$html->setvar("bannerId", $this->m_db->DLookUp("SELECT bannerId FROM options"));

		if ($this->sMessage != "")
		{
			$html->setvar("sMessage", $this->sMessage);
			if ($html->blockexists($this->m_name . "_bMessage"))
				$html->parse($this->m_name . "_bMessage");
		}

		parent::parseBlock(&$html);
	}


}


$page = new CAdminPage("", "../html/admin/banner.html");
$page->add(new CAdminHeader("iHeader", "../html/admin/header.html"));
$page->add(new CHtmlBlock("iFooter", "../html/admin/footer.html"));

$banner = new CBannerForm($db, "banner", null);
$page->add($banner);

$page->init();
$page->action();
$page->parse(null);




?>
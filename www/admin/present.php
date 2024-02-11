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

get_block_params("present");


$db = new CDB();
$db->connect();
loadOptions($db);



class CPresentForm extends CHtmlRecord
{

	function customValidate($cmd)
	{
		global $HTTP_POST_FILES;

		if ($cmd == "present_insert" || $cmd == "present_update")
		{
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
			} else {
				if ($cmd == $this->m_name . "_insert")
					return "Картинка обязательна";
			}
		}		
		return "";
	}

	function customAction($cmd)
	{
		global $HTTP_POST_FILES;

		if ($cmd == $this->m_name . "_insert" || $cmd == $this->m_name . "_update")
		{
			$name = "picture";
			$cant = 0;
			if (isset($HTTP_POST_FILES[$name]) && is_uploaded_file($HTTP_POST_FILES[$name]["tmp_name"]))
			{

				$sFile_ = PRESENTS_DIR . $this->m_id;
				$im = new Image();

				if ($im->loadImage($HTTP_POST_FILES[$name]['tmp_name']))
				{
					$im->resizeCropped(PRESENT_BIG_X, PRESENT_BIG_Y, "", 0);
					$im->saveImage($sFile_ . ".jpg", IMAGE_QUALITY);
				} else $cant = 1;

				if ($im->loadImage($HTTP_POST_FILES[$name]['tmp_name']))
				{
					$im->resizeCropped(PRESENT_SMALL_X, PRESENT_SMALL_Y, "", 0);
					$im->saveImage($sFile_ . "_s.jpg", IMAGE_QUALITY);
				} else $cant = 1;

			}
		}

		if ($cmd == $this->m_name . "_delete")
		{
			$sFile_ = PRESENTS_DIR . $this->m_id;
			@unlink($sFile_ . ".jpg");
			@unlink($sFile_ . "_s.jpg");
		}

		return "";
	}

	function parseBlock(&$html)
	{
		if ($this->m_id > 0)
		{
			$html->setvar("presentId", $this->m_id);
			$html->parse("present_picture");
		}

		parent::parseBlock(&$html);
	}


}


$page = new CAdminPage("", "../html/admin/present.html");
$page->add(new CAdminHeader("iHeader", "../html/admin/header.html"));
$page->add(new CHtmlBlock("iFooter", "../html/admin/footer.html"));

$present = new CPresentForm($db, "present", null, "presents", "FROM presents WHERE presentId=", "presents.php?");
$present->m_fields["title"] = Array ("title" => "Название", "value" => "", "min" => 2, "max" => 128);
$present->m_fields["priority"] = Array ("title" => "Приоритет", "value" => "0", "min" => 0, "max" => 100);
$page->add($present);

$page->init();
$page->action();
$page->parse(null);




?>
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



class CMessageForm extends CHtmlBlock
{
	function CMessageForm($name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
	}

	function parseBlock(&$html)
	{
		$mes = get_param("mes", "");
		if ($mes != "")
			if ($html->blockexists($mes))
				$html->parse($mes);

		parent::parseBlock(&$html);
	}

}



$db = new CDB();
$db->connect();
loadOptions($db);

$page = new CCommonPage("", "html/public/message.html");
$page->add(new CCommonHeader($db, "iHeader", "html/public/header.html"));
$page->add(new CHtmlBlock("iFooter", "html/public/footer.html"));
$page->add(new CMessageForm("message", null));

$page->add(new CLider($db, "lider", null));

$searchForm = new CSimpleSearchForm($db, "search", "html/public/searchSimple.html");
$searchForm->m_withPic = 1;
$page->add($searchForm);


$page->init();
$page->action();
$page->parse(null);





?>
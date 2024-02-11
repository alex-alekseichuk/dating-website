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


$page = new CPublicPage("", "html/public/index.html");
$page->add(new CCommonHeader($db, "iHeader", "html/public/header.html"));
$page->add(new CHtmlBlock("iFooter", "html/public/footer.html"));
$searchForm = new CSimpleSearchForm($db, "search", "html/public/searchSimple.html");
$searchForm->m_withPic = 1;
$page->add($searchForm);


$page->add(new CLider($db, "lider", null));


if ($g_options["new10"] > 0)
{
	$new10 = new CNew10Grid($db, "new10", null);
	$new10->m_fields["userId"] = Array ("userId", null, "");
	$new10->m_fields["login"] = Array ("login", null, "");
	$new10->m_fields["age"] = Array ("age", null, "");
	$new10->m_fields["sex"] = Array ("sex", null, "");
	$new10->m_fields["lookSex"] = Array ("lookSex", null, "");
	$new10->m_fields["city"] = Array ("city", null, "");
	$new10->m_fields["about"] = Array ("about", null, "");
	$new10->m_fields["picId"] = Array ("picId", null, "");
	$new10->m_fields["img"] = Array ("img", "no.jpg", "");
	$new10->m_fields["bVIP"] = Array ("bVIP", null, "");
	$new10->m_itemBlocks["vip"] = 0;
	$new10->m_itemBlocks["novip"] = 0;
	$page->add($new10);
}
if ($g_options["top10"] > 0)
{
	$top10 = new CTop10Grid($db, "top10", null);
	$top10->m_fields["userId"] = Array ("userId", null, "");
	$top10->m_fields["login"] = Array ("login", null, "");
	$top10->m_fields["age"] = Array ("age", null, "");
	$top10->m_fields["sex"] = Array ("sex", null, "");
	$top10->m_fields["lookSex"] = Array ("lookSex", null, "");
	$top10->m_fields["city"] = Array ("city", null, "");
	$top10->m_fields["about"] = Array ("about", null, "");
	$top10->m_fields["picId"] = Array ("picId", null, "");
	$top10->m_fields["img"] = Array ("img", "no.jpg", "");
	$top10->m_fields["bVIP"] = Array ("bVIP", null, "");
	$top10->m_itemBlocks["vip"] = 0;
	$top10->m_itemBlocks["novip"] = 0;
	$page->add($top10);
}
if ($g_options["view10"] > 0)
{
	$view10 = new CView10Grid($db, "view10", null);
	$view10->m_fields["userId"] = Array ("userId", null, "");
	$view10->m_fields["login"] = Array ("login", null, "");
	$view10->m_fields["age"] = Array ("age", null, "");
	$view10->m_fields["sex"] = Array ("sex", null, "");
	$view10->m_fields["lookSex"] = Array ("lookSex", null, "");
	$view10->m_fields["city"] = Array ("city", null, "");
	$view10->m_fields["about"] = Array ("about", null, "");
	$view10->m_fields["picId"] = Array ("picId", null, "");
	$view10->m_fields["img"] = Array ("img", "no.jpg", "");
	$view10->m_fields["bVIP"] = Array ("bVIP", null, "");
	$view10->m_itemBlocks["vip"] = 0;
	$view10->m_itemBlocks["novip"] = 0;
	$page->add($view10);
}


if ($g_options["bannerUrl"] != "")
{
	$banner = new CBannerView($db, "bannerView", null);
	$page->add($banner);
}


$page->init();
$page->action();
$page->parse(null);



?>
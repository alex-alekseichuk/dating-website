<?php


include_once("../include/core.php");
include_once("../include/db.php");
include_once("../include/html.php");
include_once("../include/params.php");
include_once("../include/block.php");
include_once("../include/grid.php");
include_once("../include/common.php");
include_once("../include/admin.php");

get_block_params("cities");





$db = new CDB();
$db->connect();
loadOptions($db);

$page = new CAdminPage("", "../html/admin/cities.html");
$page->add(new CAdminHeader("iHeader", "../html/admin/header.html"));
$page->add(new CHtmlBlock("iFooter", "../html/admin/footer.html"));

$cities = new CHtmlGrid($db, "cities", null);
$cities->m_sqlcount = "select count(*) as cnt from cities";
$cities->m_sql = "select cityId,title,sGeoipCity,points100 from cities";
$cities->m_fields["cityId"] = Array ("cityId", null, "");
$cities->m_fields["link"] = Array ("link", "city.php?cityId=", "");
$cities->m_fields["title"] = Array ("title", null, "");
$cities->m_fields["sGeoipCity"] = Array ("sGeoipCity", null, "");
$cities->m_fields["points100"] = Array ("points100", null, "");
$page->add($cities);


$page->init();
$page->action();
$page->parse(null);






?>
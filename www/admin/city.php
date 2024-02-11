<?php


include_once("../include/core.php");
include_once("../include/db.php");
include_once("../include/html.php");
include_once("../include/params.php");
include_once("../include/block.php");
include_once("../include/grid.php");
include_once("../include/record.php");
include_once("../include/common.php");
include_once("../include/admin.php");

get_block_params("city");


$db = new CDB();
$db->connect();
loadOptions($db);

$page = new CAdminPage("", "../html/admin/city.html");
$page->add(new CAdminHeader("iHeader", "../html/admin/header.html"));
$page->add(new CHtmlBlock("iFooter", "../html/admin/footer.html"));

//$db, $name, $html_path, $table, $sqlFromWhere, $return_page
$city = new CHtmlRecord($db, "city", null, "cities", "FROM cities WHERE cityId=", "cities.php?");
$city->m_fields["title"] = Array ("title" => "Город", "value" => "", "min" => 2, "max" => 64);
$city->m_fields["sGeoipCity"] = Array ("title" => "GeoIP имя", "value" => "", "min" => 2, "max" => 64, "optional"=>1);
$city->m_fields["points100"] = Array ("title" => "100 баллов", "type"=>"float", "value" => "5", "min" => 0.01, "max" => 1000);
$page->add($city);

$page->init();
$page->action();
$page->parse(null);




?>
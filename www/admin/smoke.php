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

get_block_params("smoke");


$db = new CDB();
$db->connect();
loadOptions($db);

$page = new CAdminPage("", "../html/admin/smoke.html");
$page->add(new CAdminHeader("iHeader", "../html/admin/header.html"));
$page->add(new CHtmlBlock("iFooter", "../html/admin/footer.html"));

$smoke = new CHtmlRecord($db, "smoke", null, "smokes", "FROM smokes WHERE smokeId=", "smokes.php?");
$smoke->m_fields["title"] = Array ("title" => "Курение", "value" => "", "min" => 2, "max" => 64);
$smoke->m_fields["priority"] = Array ("title" => "Приоритет", "value" => "0", "min" => 0, "max" => 100);
$page->add($smoke);

$page->init();
$page->action();
$page->parse(null);




?>
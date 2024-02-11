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

get_block_params("alcohol");


$db = new CDB();
$db->connect();
loadOptions($db);

$page = new CAdminPage("", "../html/admin/alcohol.html");
$page->add(new CAdminHeader("iHeader", "../html/admin/header.html"));
$page->add(new CHtmlBlock("iFooter", "../html/admin/footer.html"));

$alcohol = new CHtmlRecord($db, "alcohol", null, "alcohols", "FROM alcohols WHERE alcoholId=", "alcohols.php?");
$alcohol->m_fields["title"] = Array ("title" => "Текст", "value" => "", "min" => 2, "max" => 64);
$alcohol->m_fields["priority"] = Array ("title" => "Приоритет", "value" => "0", "min" => 0, "max" => 100);
$page->add($alcohol);

$page->init();
$page->action();
$page->parse(null);




?>
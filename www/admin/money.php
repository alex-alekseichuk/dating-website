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

get_block_params("money");


$db = new CDB();
$db->connect();
loadOptions($db);

$page = new CAdminPage("", "../html/admin/money.html");
$page->add(new CAdminHeader("iHeader", "../html/admin/header.html"));
$page->add(new CHtmlBlock("iFooter", "../html/admin/footer.html"));

//$db, $name, $html_path, $table, $sqlFromWhere, $return_page
$money = new CHtmlRecord($db, "money", null, "money", "FROM money WHERE moneyId=", "moneys.php?");
$money->m_fields["title"] = Array ("title" => "Материальное положение", "value" => "", "min" => 2, "max" => 64);
$money->m_fields["priority"] = Array ("title" => "Приоритет", "value" => "0", "min" => 0, "max" => 100);
$page->add($money);

$page->init();
$page->action();
$page->parse(null);




?>
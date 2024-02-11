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

get_block_params("interes");


$db = new CDB();
$db->connect();
loadOptions($db);

$page = new CAdminPage("", "../html/admin/interes.html");
$page->add(new CAdminHeader("iHeader", "../html/admin/header.html"));
$page->add(new CHtmlBlock("iFooter", "../html/admin/footer.html"));

$interes = new CHtmlRecord($db, "interes", null, "intereses", "FROM intereses WHERE interesId=", "intereses.php?");
$interes->m_fields["maskId"] = Array ("title" => "ID маски", "type"=>"int", "value" => "1", "min" => 1, "max" => 32, "unique"=>1);
$interes->m_fields["title"] = Array ("title" => "Текст", "value" => "", "min" => 2, "max" => 64);
$interes->m_fields["priority"] = Array ("title" => "Приоритет", "value" => "0", "min" => 0, "max" => 100);
$page->add($interes);

$page->init();
$page->action();
$page->parse(null);




?>
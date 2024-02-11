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

get_block_params("lang");


$db = new CDB();
$db->connect();
loadOptions($db);

$page = new CAdminPage("", "../html/admin/lang.html");
$page->add(new CAdminHeader("iHeader", "../html/admin/header.html"));
$page->add(new CHtmlBlock("iFooter", "../html/admin/footer.html"));

$lang = new CHtmlRecord($db, "lang", null, "langs", "FROM langs WHERE langId=", "langs.php?");
$lang->m_fields["maskId"] = Array ("title" => "ID маски", "type"=>"int", "value" => "1", "min" => 1, "max" => 32, "unique"=>1);
$lang->m_fields["title"] = Array ("title" => "язык", "value" => "", "min" => 2, "max" => 64);
$lang->m_fields["priority"] = Array ("title" => "ѕриоритет", "value" => "0", "min" => 0, "max" => 100);
$page->add($lang);

$page->init();
$page->action();
$page->parse(null);




?>
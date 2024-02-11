<?php


include_once("../include/core.php");
include_once("../include/db.php");
include_once("../include/html.php");
include_once("../include/params.php");
include_once("../include/block.php");
include_once("../include/grid.php");
include_once("../include/common.php");
include_once("../include/admin.php");

get_block_params("presents");


$db = new CDB();
$db->connect();
loadOptions($db);

$page = new CAdminPage("", "../html/admin/presents.html");
$page->add(new CAdminHeader("iHeader", "../html/admin/header.html"));
$page->add(new CHtmlBlock("iFooter", "../html/admin/footer.html"));

$items = new CHtmlGrid($db, "presents", null);
$items->m_sqlcount = "select count(*) as cnt from presents";
$items->m_sql = "select presentId,title,priority from presents";
$items->m_fields["presentId"] = Array ("presentId", null, "");
$items->m_fields["link"] = Array ("link", "present.php?presentId=", "");
$items->m_fields["title"] = Array ("title", null, "");
$items->m_fields["priority"] = Array ("priority", null, "");
$page->add($items);


$page->init();
$page->action();
$page->parse(null);



?>
<?php


include_once("../include/core.php");
include_once("../include/db.php");
include_once("../include/html.php");
include_once("../include/params.php");
include_once("../include/block.php");
include_once("../include/grid.php");
include_once("../include/common.php");
include_once("../include/admin.php");

get_block_params("homes");





$db = new CDB();
$db->connect();
loadOptions($db);

$page = new CAdminPage("", "../html/admin/homes.html");
$page->add(new CAdminHeader("iHeader", "../html/admin/header.html"));
$page->add(new CHtmlBlock("iFooter", "../html/admin/footer.html"));

$items = new CHtmlGrid($db, "homes", null);
$items->m_sqlcount = "select count(*) as cnt from homes";
$items->m_sql = "select homeId,title,priority from homes";
$items->m_fields["homeId"] = Array ("homeId", null, "");
$items->m_fields["link"] = Array ("link", "home.php?homeId=", "");
$items->m_fields["title"] = Array ("title", null, "");
$items->m_fields["piority"] = Array ("priority", null, "");
$page->add($items);


$page->init();
$page->action();
$page->parse(null);






?>
<?php


include_once("../include/core.php");
include_once("../include/db.php");
include_once("../include/html.php");
include_once("../include/params.php");
include_once("../include/block.php");
include_once("../include/grid.php");
include_once("../include/common.php");
include_once("../include/admin.php");

get_block_params("langs");


$db = new CDB();
$db->connect();
loadOptions($db);

$page = new CAdminPage("", "../html/admin/langs.html");
$page->add(new CAdminHeader("iHeader", "../html/admin/header.html"));
$page->add(new CHtmlBlock("iFooter", "../html/admin/footer.html"));

$items = new CHtmlGrid($db, "langs", null);
$items->m_sqlcount = "select count(*) as cnt from langs";
$items->m_sql = "select langId,maskId,title,priority from langs";
$items->m_fields["langId"] = Array ("langId", null, "");
$items->m_fields["maskId"] = Array ("maskId", null, "");
$items->m_fields["link"] = Array ("link", "lang.php?langId=", "");
$items->m_fields["title"] = Array ("title", null, "");
$items->m_fields["piority"] = Array ("priority", null, "");
$page->add($items);


$page->init();
$page->action();
$page->parse(null);



?>
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

get_block_params("vote");


$db = new CDB();
$db->connect();
loadOptions($db);

$page = new CAdminPage("", "../html/admin/vote.html");
$page->add(new CAdminHeader("iHeader", "../html/admin/header.html"));
$page->add(new CHtmlBlock("iFooter", "../html/admin/footer.html"));

$vote = new CHtmlRecord($db, "vote", null, "votes", "FROM votes WHERE voteId=", "votes.php?");
$vote->m_fields["title"] = Array ("title" => "Ответ", "value" => "", "min" => 2, "max" => 64);
$vote->m_fields["cnt"] = Array ("title" => "Счетчик", "value" => "0", "min" => 0, "max" => 100000);
$vote->m_fields["priority"] = Array ("title" => "Приоритет", "value" => "0", "min" => 0, "max" => 100);
$page->add($vote);

$page->init();
$page->action();
$page->parse(null);


?>
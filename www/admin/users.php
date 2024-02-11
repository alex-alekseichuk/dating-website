<?php


include_once("../include/core.php");
include_once("../include/db.php");
include_once("../include/html.php");
include_once("../include/params.php");
include_once("../include/block.php");
include_once("../include/grid.php");
include_once("../include/common.php");
include_once("../include/admin.php");

get_block_params("users");




class CUsersGrid extends CHtmlGrid
{
	function onItem()
	{
		global $g_sex;
		global $g_lookSex;

		$this->m_fields["sex"][2] = $g_sex[$this->m_fields["sex"][2]];
		$this->m_fields["lookSex"][2] = $g_lookSex[$this->m_fields["lookSex"][2]];
		$this->m_fields["bVIP"][2] = $this->m_fields["bVIP"][2] == "Y" ? "VIP" : "";
	}	

}




$db = new CDB();
$db->connect();
loadOptions($db);

$page = new CAdminPage("", "../html/admin/users.html");
$page->add(new CAdminHeader("iHeader", "../html/admin/header.html"));
$page->add(new CHtmlBlock("iFooter", "../html/admin/footer.html"));

$users = new CUsersGrid($db, "users", null);
$users->m_sqlcount = "select count(*) as cnt from users";
$users->m_sql = "select userId,login,email,passwd,account,registered,bVIP,sex,lookSex from users";
$users->m_fields["userId"] = Array ("userId", null, "");
$users->m_fields["link"] = Array ("link", "user.php?userId=", "");
$users->m_fields["login"] = Array ("login", null, "");
$users->m_fields["email"] = Array ("email", null, "");
$users->m_fields["account"] = Array ("account", null, "");
$users->m_fields["passwd"] = Array ("passwd", null, "");
$users->m_fields["sex"] = Array ("sex", null, "");
$users->m_fields["lookSex"] = Array ("lookSex", null, "");
$users->m_fields["bVIP"] = Array ("bVIP", null, "");
$users->m_fields["registered"] = Array ("registered", null, "");
$page->add($users);


$page->init();
$page->action();
$page->parse(null);






?>
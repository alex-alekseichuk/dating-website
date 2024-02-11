<?php


include_once("../include/core.php");
include_once("../include/db.php");
include_once("../include/html.php");
include_once("../include/params.php");
include_once("../include/block.php");
//include_once("../include/grid.php");
include_once("../include/common.php");
include_once("../include/admin.php");


//get_block_params("pGrid");



class CLogin extends CHtmlBlock
{
	var $m_db = null;
	var $sMessage = "";

	function CLogin($db, $name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
		$this->m_db = $db;
	}

	function init()
	{
		parent::init();
	}


	function action()
	{
		//$this->CHtmlBlock_action();

		$cmd = get_param("cmd", "");
		if ($cmd == "login")
		{
			$password = get_param("password", "");
			if ($password == ADMIN_PASSWD)
			{
				set_session("_admin", "admin");
				header("Location: users.php\n");
				exit;			
			} else {
				$this->sMessage = "Неправильный пароль";
			}

		}
		if ($cmd == "logout")
		{
			set_session("_admin", "");
			$this->sMessage = "Вы вышли";
		}
	}


	function parseBlock(&$html)
	{
		$html->setvar("sMessage", $this->sMessage);
		parent::parseBlock(&$html);
	}




}



$db = new CDB();
$db->connect();
loadOptions($db);

$page = new CLogin($db, "", "../html/admin/login.html");
$page->add(new CAdminHeader("iHeader", "../html/admin/header.html"));
$page->add(new CHtmlBlock("iFooter", "../html/admin/footer.html"));


$page->init();
$page->action();
$page->parse(null);





?>
<?php


include_once("include/core.php");
include_once("include/db.php");
include_once("include/html.php");
include_once("include/params.php");
include_once("include/block.php");
include_once("include/grid.php");
include_once("include/common.php");
include_once("include/public.php");
include_once("include/home.php");




class CLoginForm extends CHtmlBlock
{
	var $m_db;
	var $sMessage = "";
	var $login = "";
	function CLoginForm($db, $name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
		$this->m_db = $db;
	}


	function action()
	{
		//$this->CHtmlBlock_action();

		$cmd = get_param("cmd", "");
		if ($cmd == "login")
		{
			$this->sMessage = "Некорректные логин/пароль";

			$this->login = get_param("login", "");
			$passwd = get_param("passwd", "");
			if ($this->m_db->query("SELECT floor((TO_DAYS(now())-TO_DAYS(u.birth))/365) as age, u.bVIP, u.bActive, u.login, u.passwd, u.userId, u.sex, u.lookSex, u.cityId, c.title as city FROM (users AS u LEFT JOIN cities as c ON u.cityId=c.cityId) WHERE u.login=" . to_sql($this->login, "")))
			{
				if ($row = $this->m_db->fetch_row())
				{
					if ($row["passwd"] == $passwd)
					{
						if ($row["bActive"] == 'N')
						{
							$this->sMessage = "Ваша регистрация еще не подтверждена";
						} else {
							set_session("_userId", $row["userId"]);
							set_session("_login", $row["login"]);
							set_session("_sex", $row["sex"]);
							set_session("_lookSex", $row["lookSex"]);
							set_session("_cityId", $row["cityId"]);
							set_session("_bVIP", $row["bVIP"]);
							set_session("_age", $row["age"]);
							set_session("_city", $row["city"]);
							header("Location: home.php\n");
							exit;
						}
					}
				}
				$this->m_db->free_result();
			}

		}
		if ($cmd == "logout")
		{
			$this->m_db->execute("UPDATE users SET lastAccess=null WHERE userId=" . to_sql(get_session("_userId"), "Number"));
			set_session("_userId", "");
			set_session("_login", "");
			set_session("_sex", "");
			set_session("_lookSex", "");
			set_session("_bVIP", "");
			set_session("_age", "");
			set_session("_city", "");
			$this->sMessage = "";
		}
	}


	function parseBlock(&$html)
	{
		if ($this->sMessage != "")
		{
			$html->setvar("sMessage", $this->sMessage);
			$html->parse("bMessage");
		}
		$html->setvar("login", $this->login);
		parent::parseBlock(&$html);
	}


}



$db = new CDB();
$db->connect();
loadOptions($db);

$page = new CCommonPage("", "html/public/login.html");
$page->add(new CCommonHeader($db, "iHeader", "html/public/header.html"));
$page->add(new CHtmlBlock("iFooter", "html/public/footer.html"));
$page->add(new CLoginForm($db, "fLogin", null));

$page->add(new CLider($db, "lider", null));


if ($g_options["new10"] > 0)
{
	$new10 = new CNew10Grid($db, "new10", null);
	$new10->m_fields["userId"] = Array ("userId", null, "");
	$new10->m_fields["login"] = Array ("login", null, "");
	$new10->m_fields["age"] = Array ("age", null, "");
	$new10->m_fields["sex"] = Array ("sex", null, "");
	$new10->m_fields["lookSex"] = Array ("lookSex", null, "");
	$new10->m_fields["city"] = Array ("city", null, "");
	$new10->m_fields["about"] = Array ("about", null, "");
	$new10->m_fields["picId"] = Array ("picId", null, "");
	$new10->m_fields["img"] = Array ("img", "no.jpg", "");
	$new10->m_fields["bVIP"] = Array ("bVIP", null, "");
	$new10->m_itemBlocks["vip"] = 0;
	$new10->m_itemBlocks["novip"] = 0;
	$page->add($new10);
}
if ($g_options["top10"] > 0)
{
	$top10 = new CTop10Grid($db, "top10", null);
	$top10->m_fields["userId"] = Array ("userId", null, "");
	$top10->m_fields["login"] = Array ("login", null, "");
	$top10->m_fields["age"] = Array ("age", null, "");
	$top10->m_fields["sex"] = Array ("sex", null, "");
	$top10->m_fields["lookSex"] = Array ("lookSex", null, "");
	$top10->m_fields["city"] = Array ("city", null, "");
	$top10->m_fields["about"] = Array ("about", null, "");
	$top10->m_fields["picId"] = Array ("picId", null, "");
	$top10->m_fields["img"] = Array ("img", "no.jpg", "");
	$top10->m_fields["bVIP"] = Array ("bVIP", null, "");
	$top10->m_itemBlocks["vip"] = 0;
	$top10->m_itemBlocks["novip"] = 0;
	$page->add($top10);
}
if ($g_options["view10"] > 0)
{
	$view10 = new CView10Grid($db, "view10", null);
	$view10->m_fields["userId"] = Array ("userId", null, "");
	$view10->m_fields["login"] = Array ("login", null, "");
	$view10->m_fields["age"] = Array ("age", null, "");
	$view10->m_fields["sex"] = Array ("sex", null, "");
	$view10->m_fields["lookSex"] = Array ("lookSex", null, "");
	$view10->m_fields["city"] = Array ("city", null, "");
	$view10->m_fields["about"] = Array ("about", null, "");
	$view10->m_fields["picId"] = Array ("picId", null, "");
	$view10->m_fields["img"] = Array ("img", "no.jpg", "");
	$view10->m_fields["bVIP"] = Array ("bVIP", null, "");
	$view10->m_itemBlocks["vip"] = 0;
	$view10->m_itemBlocks["novip"] = 0;
	$page->add($view10);
}

$searchForm = new CSimpleSearchForm($db, "search", "html/public/searchSimple.html");
$searchForm->m_withPic = 1;
$page->add($searchForm);

$page->init();
$page->action();
$page->parse(null);





?>
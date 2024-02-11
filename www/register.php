<?php


include_once("include/core.php");
include_once("include/db.php");
include_once("include/html.php");
include_once("include/params.php");
include_once("include/block.php");
include_once("include/image.php");
include_once("include/grid.php");
include_once("include/record.php");
include_once("include/common.php");
include_once("include/public.php");
include_once("include/home.php");

class CRegForm extends CHtmlRecord
{
	function init()
	{
		parent::init();
		if (get_param("mes", "") == "pic")
			$this->sMessage .= "Для просмотра Фото Вы должны зарегистрироваться.<br><br>";
		if (get_param("mes", "") == "video")
			$this->sMessage .= "Для просмотра Видео Вы должны зарегистрироваться.<br><br>";
	}

	function customValidate($cmd)
	{
		global $g_options;

		if ($cmd == $this->m_name . "_insert")
		{
			if ($this->m_db->DLookUp("SELECT count(*) FROM users WHERE login=" . to_sql($this->m_fields["login"]["value"], "")) > 0)
			{
				$this->sMessage .= "Пользователь с таким Логином уже зарегистрирован.<br>Выберите другой Логин пожалуйста.<br><br>";
				$cmd = "";
				return;
			}

			$ret = validateFoto($g_options["pic"]);
			if ($g_options["pic"] == 1 && ($ret != ""))
			{
				$this->sMessage .= $ret . "<br>";
				$cmd = "";
				return;
			}


			srand(time());
			$sRegCode = time() . rand();

			if ($cmd == $this->m_name . "_insert")
			{
				$this->m_fields["sCode"]["value"] = $sRegCode;
			}
		}
	}

	function customAction($cmd)
	{
		if ($cmd == $this->m_name . "_insert")
		{
			uploadFoto($this->m_db, $this->m_id, "", 1);

			$html = new CHtml();
			if ($html->LoadTemplate("email/register.mail" , "main"))
			{
				$html->setvar("SERVER_URL" , SERVER_URL);
				$html->setvar("userId", $this->m_id);
				$html->setvar("sCode", $this->m_fields["sCode"]["value"]);
				$html->parse("main");
				$text = $html->getvar("main");

				send_email(
					$this->m_fields["email"]["value"],
					INFO_EMAIL,
					"Регистрация на FindYourDream.ru",
					$text,
					"text/html; charset=windows-1251");
			}
		}
	}

	function parseBlock(&$html)
	{
		global $g_options;

		if ($g_options["pic"] == 1)
		{
			$html->parse("bPictureCheck");
		}

		$html->setvar("MAX_FILE_SIZE", $g_options["picSize"] * 1024);

		parent::parseBlock(&$html);
	}
}



$db = new CDB();
$db->connect();
loadOptions($db);

$page = new CPublicPage("", "html/public/register.html");
$page->add(new CCommonHeader($db, "iHeader", "html/public/header.html"));
$page->add(new CHtmlBlock("iFooter", "html/public/footer.html"));

$user = new CRegForm($db, "user", null, "users", "FROM users WHERE userId=", "message.php?mes=reg1&");
$user->m_fields["login"] = Array ("title" => "Логин", "value" => "", "min" => 2, "max" => 32);
$user->m_fields["passwd"] = Array ("title" => "Пароль", "value" => "", "min" => 5, "max" => 32);
$user->m_fields["email"] = Array ("title" => "Email", "value" => "", "min" => 7, "max" => 80);
$user->m_fields["sex"] = Array ("title" => "Ваш пол", "type" => "slov", "value" => "", "options" => $g_sex);
$user->m_fields["lookSex"] = Array ("title" => "Искомый пол", "type" => "slov", "value" => "", "options" => $g_lookSex);
$user->m_fields["cityId"] = Array ("title" => "Город", "type"=>"iselect", "value" => "", "sql"=>"SELECT cityId, title FROM cities ORDER BY priority,title", "sqlcheck"=>"SELECT count(*) FROM cities WHERE cityId=");
//$user->m_fields["picture"] = Array ("title" => "Фото", "value" => "", "type" => "file", "dir" => PICS_DIR, "file" => "p_%rand%");
$user->m_fields["birth"] = Array ("title" => "День рождения", "type"=>"date3", "value" => "");
$user->m_fields["registered"] = Array ("plain" => "now()", "type"=>"plain", "noupdate"=>1, "nohttp"=>1, "nocheck"=>1);
$user->m_fields["sCode"] = Array ("type"=>"", "noupdate"=>1, "nohttp"=>1, "nocheck"=>1);
$page->add($user);

$page->add(new CLider($db, "lider", null));

$searchForm = new CSimpleSearchForm($db, "search", "html/public/searchSimple.html");
$searchForm->m_withPic = 1;
$page->add($searchForm);


$page->init();
$page->action();
$page->parse(null);





?>
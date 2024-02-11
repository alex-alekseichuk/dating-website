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

class CForgotForm extends CHtmlRecord
{
	function action()
	{
		if (get_param("cmd", "") == "forgot")
		{
			$login = $this->m_fields["login"]["value"];
			$email = $this->m_fields["email"]["value"];
			$passwd = $this->m_db->DLookUp("SELECT passwd FROM users WHERE login=" . to_sql($login, "Number") . " AND email=" . to_sql($email, "Number"));
			if ($passwd != "" && $passwd != 0)
			{
				$html = new CHtml();
				if ($html->LoadTemplate("email/forgot.mail" , "main"))
				{
					$html->setvar("SERVER_URL" , SERVER_URL);
					$html->setvar("login", $login);
					$html->setvar("email", $email);
					$html->setvar("passwd", $passwd);
					$html->parse("main");
					$text = $html->getvar("main");

					send_email(
						$email,
						INFO_EMAIL,
						"Пароль для доступа на FindYourDream.ru",
						$text,
						"text/html; charset=windows-1251");
				}
			} else {
				$this->sMessage = "Некорректные Логин и Email.";
			}
		}
	}

}



$db = new CDB();
$db->connect();
loadOptions($db);

$page = new CPublicPage("", "html/public/forgot_passwd.html");
$page->add(new CCommonHeader($db, "iHeader", "html/public/header.html"));
$page->add(new CHtmlBlock("iFooter", "html/public/footer.html"));

$user = new CForgotForm($db, "forgot", null, "users", "FROM users WHERE userId=", "message.php?mes=passwd1&");
$user->m_fields["login"] = Array ("title" => "Логин", "value" => "", "min" => 2, "max" => 32, "nodb"=>1);
$user->m_fields["email"] = Array ("title" => "Email", "value" => "", "min" => 7, "max" => 80, "nodb"=>1);
$user->m_id = 0;
$page->add($user);

$page->add(new CLider($db, "lider", null));

$searchForm = new CSimpleSearchForm($db, "search", "html/public/searchSimple.html");
$searchForm->m_withPic = 1;
$page->add($searchForm);

$page->init();
$page->action();
$page->parse(null);


?>
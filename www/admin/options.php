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

//get_block_params("options");



class COptions extends CHtmlBlock
{
	var $m_db = null;
	var $m_fields = Array();
	var $sMessage  = "";

	function COptions($db, $name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
		$this->m_db = $db;
	}


	function init()
	{
		global $g_liderTypes;

		$this->m_fields["pic"] = Array ("title" => "Фото обязательно", "type"=>"check", "value" => "0", "optional" => 1);
		$this->m_fields["picSize"] = Array ("title" => "Max. размер фото", "type"=>"int", "value" => "1024", "min" => 1, "max" => 10240);
		$this->m_fields["videoSize"] = Array ("title" => "Max. размер видео", "type"=>"int", "value" => "2", "min" => 1, "max" => 10);
		$this->m_fields["picNum"] = Array ("title" => "Max. кол-во фото", "type"=>"int", "value" => "20", "min" => 1, "max" => 50);
		$this->m_fields["videoNum"] = Array ("title" => "Max. кол-во видео", "type"=>"int", "value" => "10", "min" => 1, "max" => 50);
		$this->m_fields["videoVIP"] = Array ("title" => "Видео только для VIP", "type"=>"check", "value" => "0", "optional" => 1);
		$this->m_fields["liderTypeId"] = Array ("title" => "Тип лидера", "type" => "ilov", "value" => "0", "options" => $g_liderTypes);
		$this->m_fields["nLiders"] = Array ("title" => "Кол-во лидеров", "type"=>"int", "value" => "4", "min" => 1, "max" => 10);
		$this->m_fields["gameTimeout"] = Array ("title" => "Игровой период", "type"=>"int", "value" => "1", "min" => 1, "max" => 10);
		$this->m_fields["gamePrice"] = Array ("title" => "Игровая ставка", "type"=>"int", "value" => "1", "min" => 1, "max" => 10);
		$this->m_fields["points100"] = Array ("title" => "100 баллов", "type"=>"float", "value" => "5", "min" => 0.01, "max" => 1000);

		$this->m_fields["top10"] = Array ("title" => "По просмотрам на главной", "type"=>"int", "value" => "0", "min" => 0, "max" => 25);
		$this->m_fields["new10"] = Array ("title" => "Новые на главной", "type"=>"int", "value" => "0", "min" => 0, "max" => 25);
		$this->m_fields["view10"] = Array ("title" => "По сообщениям на главной", "type"=>"int", "value" => "0", "min" => 0, "max" => 25);

		$this->m_fields["emailPrice"] = Array ("title" => "Цена открытия email'а", "type"=>"float", "value" => "0.5", "min" => 0.01, "max" => 1000);
		$this->m_fields["sendPrice"] = Array ("title" => "Цена рассылки", "type"=>"float", "value" => "1", "min" => 0.01, "max" => 1000);
		$this->m_fields["ratingUpPrice"] = Array ("title" => "Цена поднятия рейтинга", "type"=>"float", "value" => "1", "min" => 0.01, "max" => 1000);
		$this->m_fields["ratingFreezeDays"] = Array ("title" => "Бесплатное поднятие рейтинга", "type"=>"int", "value" => "5", "min" => 1, "max" => 50);
		$this->m_fields["presentPrice"] = Array ("title" => "Цена подарка", "type"=>"float", "value" => "1", "min" => 0.01, "max" => 1000);

		if (! rec_get_db($this->m_db, $this->m_fields, " FROM options"))
		{
			$this->sMessage = "База данных не инициализирована корректно<br>";
		}

		parent::init();
	}


	function action()
	{

		$cmd = get_param("cmd", "");

		if ($cmd == "update")
		{
			$this->sMessage .= rec_get_http($this->m_db, $this->m_fields, "", "options", "options");

			$sql = "UPDATE options SET " . rec_fields_to_sql($this->m_fields);

//echo "<hr>$sql<hr>";

			if ($this->m_db->execute($sql))
			{
				$this->sMessage = "Изменения сохранены";
			}
		}

	}


	function parseBlock(&$html)
	{
		if ($this->sMessage != "")
		{
			$html->setvar("sMessage", $this->sMessage);
			if ($html->blockexists($this->m_name . "_bMessage"))
				$html->parse($this->m_name . "_bMessage");
		}

		rec_parse_values($this->m_db, $html, $this->m_fields);

		$html->setvar("checks", rec_html_checks($this->m_fields, $this->m_name));


		parent::parseBlock(&$html);
	}


}



$db = new CDB();
$db->connect();

$page = new CAdminPage("", "../html/admin/options.html");
$page->add(new CAdminHeader("iHeader", "../html/admin/header.html"));
$page->add(new CHtmlBlock("iFooter", "../html/admin/footer.html"));

$options = new COptions($db, "options", null);
$page->add($options);

$page->init();
$page->action();
$page->parse(null);




?>
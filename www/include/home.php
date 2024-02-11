<?php


class CLider extends CHtmlBlock
{
	var $m_db = null;
	var $sMessage = "";

	var $m_nLiders = 0;
	var $m_liders = Array();
	var $m_cityWhere = "";
	var $m_sqlSelect = "SELECT u.bVIP, u.userId, login, floor((TO_DAYS(now())-TO_DAYS(birth))/365) as age, c.title as city, lookSex, sex, if(sHello is null or sHello='', left(about, 32), sHello) as about, picId";
	var $m_sqlFrom = " FROM (users AS u LEFT JOIN cities as c ON u.cityId=c.cityId) LEFT JOIN pics as p ON (p.userId=u.userId AND p.bMain='Y' AND p.bApproved='Y')";
	var $m_sqlFromLiders = " FROM ((liders AS l JOIN users AS u ON l.userId=u.userId) LEFT JOIN cities as c ON u.cityId=c.cityId) LEFT JOIN pics as p ON (p.userId=u.userId AND p.bMain='Y' AND p.bApproved='Y')";
	var $m_sqlWhere = " WHERE u.bActive='Y'";


	function CLider($db, $name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
		$this->m_db = $db;
	}

	function getByViews()
	{
		global $g_options;

		$sqlSelect = $this->m_sqlSelect;
		$cityWhere = "";
		if ($this->m_db->DLookUp("SELECT COUNT(*)" . $this->m_sqlFrom . $this->m_sqlWhere . $this->m_cityWhere . " GROUP BY u.userId") > 0)
			$cityWhere = $this->m_cityWhere;

		$this->m_db->query($this->m_sqlSelect . $this->m_sqlFrom . $this->m_sqlWhere . $cityWhere .
			" GROUP BY u.userId" .
			" ORDER BY nViews DESC LIMIT " . to_sql($g_options["nLiders"], "Number"));
		while ($row = $this->m_db->fetch_row())
		{
			$a = Array();
			$a["userId"] = $row["userId"];
			$a["bVIP"] = $row["bVIP"];
			$a["login"] = $row["login"];
			$a["age"] = $row["age"];
			$a["city"] = $row["city"];
			$a["sex"] = $row["sex"];
			$a["lookSex"] = $row["lookSex"];
			$a["about"] = $row["about"];
			$a["picId"] = $row["picId"];
			$this->m_liders[$this->m_nLiders] = $a;
			$this->m_nLiders ++;
		}
		$this->m_db->free_result();
	}

	function getByMessages()
	{
		global $g_options;

		$sqlSelect = $this->m_sqlSelect;
		$cityWhere = "";
		if ($this->m_db->DLookUp("SELECT COUNT(*)" . $this->m_sqlFrom . $this->m_sqlWhere . $this->m_cityWhere . " GROUP BY u.userId") > 0)
			$cityWhere = $this->m_cityWhere;

		$this->m_db->query($this->m_sqlSelect . $this->m_sqlFrom . $this->m_sqlWhere . $cityWhere .
			" GROUP BY u.userId" .
			" ORDER BY nMessages DESC LIMIT " . to_sql($g_options["nLiders"], "Number"));
		while ($row = $this->m_db->fetch_row())
		{
			$a = Array();
			$a["userId"] = $row["userId"];
			$a["bVIP"] = $row["bVIP"];
			$a["login"] = $row["login"];
			$a["age"] = $row["age"];
			$a["city"] = $row["city"];
			$a["sex"] = $row["sex"];
			$a["lookSex"] = $row["lookSex"];
			$a["about"] = $row["about"];
			$a["picId"] = $row["picId"];
			$this->m_liders[$this->m_nLiders] = $a;
			$this->m_nLiders ++;
		}
		$this->m_db->free_result();
	}

	function parseBlock(&$html)
	{
		global $g_options;
		global $g_sex;
		global $g_lookSex;

		$this->m_cityWhere = get_session("_cityId");
		if ($this->m_cityWhere != "" && $this->m_cityWhere != 0)
			$this->m_cityWhere = " AND u.cityId=" . to_sql($this->m_cityWhere, "Number");
		else
			$this->m_cityWhere = "";

		$liderType = $g_options["liderTypeId"];


		if ($liderType == 2) // lider game
		{
			$cityWhere = $this->m_cityWhere;
			$gameType = "";
			if ($this->m_db->DLookUp("SELECT COUNT(*) FROM users as u WHERE inGame>0" . $this->m_cityWhere) > 0)
			{
				$gameType = "game";
			}
			else if ($this->m_db->DLookUp("SELECT COUNT(*) FROM liders as l JOIN users as u ON u.userId=l.userId WHERE 1=1" . $this->m_cityWhere) > 0)
			{
				$gameType = "lastLider";
			}
			if ($this->m_cityWhere != "" && $gameType == "")
			{
				$cityWhere = "";
				if ($this->m_db->DLookUp("SELECT COUNT(*) FROM users WHERE inGame>0") > 0)
				{
					$gameType = "game";
				}
				else if ($this->m_db->DLookUp("SELECT COUNT(*) FROM liders") > 0)
				{
					$gameType = "lastLider";
				}
			}

			if ($gameType == "")
			{
				$liderType = 0; // no liders, so, use by Views
			} else {
				if ($gameType == "game")
				{
					$this->m_db->query($this->m_sqlSelect . $this->m_sqlFrom . $this->m_sqlWhere . " AND inGame>0" . $cityWhere .
						" GROUP BY u.userId" .
						" ORDER BY inGame DESC");
				} else {
					$this->m_db->query($this->m_sqlSelect . $this->m_sqlFromLiders . " WHERE l.finished is not null" . $cityWhere .
						" GROUP BY u.userId" .
						" ORDER BY l.finished DESC");
				}
				while (($row = $this->m_db->fetch_row()) && $this->m_nLiders < $g_options["nLiders"])
				{
					$a = Array();
					$a["userId"] = $row["userId"];
					$a["bVIP"] = $row["bVIP"];
					$a["login"] = $row["login"];
					$a["age"] = $row["age"];
					$a["city"] = $row["city"];
					$a["sex"] = $row["sex"];
					$a["lookSex"] = $row["lookSex"];
					$a["about"] = $row["about"];
					$a["picId"] = $row["picId"];
					$this->m_liders[$this->m_nLiders] = $a;
					$this->m_nLiders ++;
				}
				$this->m_db->free_result();
			}
		}

		if ($liderType == 0) // by views
		{
			$this->getByViews();
		}
		if ($liderType == 1) // by messages
		{
			$this->getByMessages();
		}


		if ($this->m_nLiders > 0)
		{
			for ($i = 0; $i < $this->m_nLiders; $i++)
			{
				if (strlen($this->m_liders[$i]["about"]) == 32)
					$this->m_liders[$i]["about"] .= "...";
				$this->m_liders[$i]["sex"] = $g_sex[$this->m_liders[$i]["sex"]];
				$this->m_liders[$i]["lookSex"] = $g_lookSex[$this->m_liders[$i]["lookSex"]];
				if ($this->m_liders[$i]["picId"] == "")
					$this->m_liders[$i]["img"] = ($i == 0 ? "no_l.jpg" : "no_m.jpg");
				else	
					$this->m_liders[$i]["img"] = $this->m_liders[$i]["userId"] . "_" . $this->m_liders[$i]["picId"] . "_" . ($i == 0 ? "l" : "m") . ".jpg";
				if ($this->m_liders[$i]["bVIP"] == "Y")
				{
					$html->parsesafe($i == 0 ? "lider1_vip" : "lider2_vip", false);
					$html->setblockvar($i == 0 ? "lider1_novip" : "lider2_novip", "");	
				} else {
					$html->setblockvar($i == 0 ? "lider1_vip" : "lider2_vip", "");	
					$html->parsesafe($i == 0 ? "lider1_novip" : "lider2_novip", false);	
				}

				$html->setvar("numer", $i + 1);

				$html->setvar("userId", $this->m_liders[$i]["userId"]);
				$html->setvar("login", $this->m_liders[$i]["login"]);
				$html->setvar("age", $this->m_liders[$i]["age"]);
				$html->setvar("city", $this->m_liders[$i]["city"]);
				$html->setvar("sex", $this->m_liders[$i]["sex"]);
				$html->setvar("lookSex", $this->m_liders[$i]["lookSex"]);
				$html->setvar("about", $this->m_liders[$i]["about"]);
				$html->setvar("img", $this->m_liders[$i]["img"]);

				if ($i == 0)			
					$html->parsesafe("lider1");
				else
					$html->parsesafe("lider2");
			}
		}

		parent::parseBlock(&$html);
	}



}



class CTop10Grid extends CHtmlGrid
{
	function init()
	{
		global $g_options;

		parent::init();

		$city = get_session("_cityId");
		if ($city != "")
			$city = " AND u.cityId=" . to_sql($city, "Number");

		$sWhere = " WHERE bActive='Y'" . $city .
			" ORDER BY rating DESC, nMessages DESC";
		$this->m_nPerPage = $g_options["top10"];

		$this->m_sqlcount = "SELECT COUNT(*) FROM users AS u" . $sWhere;
		$this->m_sql = "SELECT u.bVIP, u.userId, login, floor((TO_DAYS(now())-TO_DAYS(birth))/365) as age, c.title as city, lookSex, sex, left(about, 32) as about, picId" .
			" FROM (users AS u LEFT JOIN cities as c ON u.cityId=c.cityId) LEFT JOIN pics as p ON (p.userId=u.userId AND p.bMain='Y' AND p.bApproved='Y')" . $sWhere;

//echo "<hr>" . $this->m_sqlcount . "<hr>";
//echo "<hr>" . $this->m_sql . "<hr>";

	}


	function onItem()
	{
		global $g_sex;
		global $g_lookSex;

		if (strlen($this->m_fields["about"][2]) == 32)
			$this->m_fields["about"][2] .= "...";

		$this->m_fields["sex"][2] = $g_sex[$this->m_fields["sex"][2]];
		$this->m_fields["lookSex"][2] = $g_lookSex[$this->m_fields["lookSex"][2]];

		if ($this->m_fields["picId"][2] == "")
			$this->m_fields["img"][2] = "no.jpg";
		else	
			$this->m_fields["img"][2] = $this->m_fields["userId"][2] . "_" . $this->m_fields["picId"][2] . "_s.jpg";

		if ($this->m_fields["bVIP"][2] == "Y")
		{
			$this->m_itemBlocks["vip"] = 1;
			$this->m_itemBlocks["novip"] = 0;
		} else {
			$this->m_itemBlocks["vip"] = 0;
			$this->m_itemBlocks["novip"] = 1;
		}
	}	

}



class CNew10Grid extends CHtmlGrid
{
	function init()
	{
		global $g_options;

		parent::init();

		$city = get_session("_cityId");
		if ($city != "")
			$city = " AND u.cityId=" . to_sql($city, "Number");

		$sWhere = " WHERE bActive='Y'" . $city .
			" ORDER BY rating DESC, registered DESC";
		$this->m_nPerPage = $g_options["new10"];

		$this->m_sqlcount = "SELECT COUNT(*) FROM users AS u" . $sWhere;
		$this->m_sql = "SELECT u.bVIP, u.userId, login, floor((TO_DAYS(now())-TO_DAYS(birth))/365) as age, c.title as city, lookSex, sex, left(about, 32) as about, picId" .
			" FROM (users AS u LEFT JOIN cities as c ON u.cityId=c.cityId) LEFT JOIN pics as p ON (p.userId=u.userId AND p.bMain='Y' AND p.bApproved='Y')" . $sWhere;

//echo "<hr>" . $this->m_sqlcount . "<hr>";
//echo "<hr>" . $this->m_sql . "<hr>";

	}


	function onItem()
	{
		global $g_sex;
		global $g_lookSex;

		if (strlen($this->m_fields["about"][2]) == 32)
			$this->m_fields["about"][2] .= "...";

		$this->m_fields["sex"][2] = $g_sex[$this->m_fields["sex"][2]];
		$this->m_fields["lookSex"][2] = $g_lookSex[$this->m_fields["lookSex"][2]];

		if ($this->m_fields["picId"][2] == "")
			$this->m_fields["img"][2] = "no.jpg";
		else	
			$this->m_fields["img"][2] = $this->m_fields["userId"][2] . "_" . $this->m_fields["picId"][2] . "_s.jpg";

		if ($this->m_fields["bVIP"][2] == "Y")
		{
			$this->m_itemBlocks["vip"] = 1;
			$this->m_itemBlocks["novip"] = 0;
		} else {
			$this->m_itemBlocks["vip"] = 0;
			$this->m_itemBlocks["novip"] = 1;
		}
	}	

}

class CView10Grid extends CHtmlGrid
{
	function init()
	{
		global $g_options;

		parent::init();

		$city = get_session("_cityId");
		if ($city != "")
			$city = " AND u.cityId=" . to_sql($city, "Number");

		$sWhere = " WHERE bActive='Y'" . $city .
			" ORDER BY rating DESC, nViews DESC";
		$this->m_nPerPage = $g_options["view10"];

		$this->m_sqlcount = "SELECT COUNT(*) FROM users AS u" . $sWhere;
		$this->m_sql = "SELECT u.bVIP, u.userId, login, floor((TO_DAYS(now())-TO_DAYS(birth))/365) as age, c.title as city, lookSex, sex, left(about, 32) as about, picId" .
			" FROM (users AS u LEFT JOIN cities as c ON u.cityId=c.cityId) LEFT JOIN pics as p ON (p.userId=u.userId AND p.bMain='Y' AND p.bApproved='Y')" . $sWhere;

//echo "<hr>" . $this->m_sqlcount . "<hr>";
//echo "<hr>" . $this->m_sql . "<hr>";

	}


	function onItem()
	{
		global $g_sex;
		global $g_lookSex;

		if (strlen($this->m_fields["about"][2]) == 32)
			$this->m_fields["about"][2] .= "...";

		$this->m_fields["sex"][2] = $g_sex[$this->m_fields["sex"][2]];
		$this->m_fields["lookSex"][2] = $g_lookSex[$this->m_fields["lookSex"][2]];

		if ($this->m_fields["picId"][2] == "")
			$this->m_fields["img"][2] = "no.jpg";
		else	
			$this->m_fields["img"][2] = $this->m_fields["userId"][2] . "_" . $this->m_fields["picId"][2] . "_s.jpg";

		if ($this->m_fields["bVIP"][2] == "Y")
		{
			$this->m_itemBlocks["vip"] = 1;
			$this->m_itemBlocks["novip"] = 0;
		} else {
			$this->m_itemBlocks["vip"] = 0;
			$this->m_itemBlocks["novip"] = 1;
		}
	}	

}




?>
<?php

//include_once("block.php");
//include_once("db.php");

class CHtmlGrid extends CHtmlBlock
{
	var $m_nPerPage = 30; // defaut number items per page

	var $m_sql = null;
	var $m_sqlcount = null;
	var $m_db = null;
	var $m_sort = "";
	var $m_dir = "";

	var $m_pageMode = 1; // 1-page by page strongly, 2-last page should be filled
	var $m_lastPageByDefault = 0; // 0-regular grid, 1-views last page by default

	// the map of columns (fields)
	var $m_fields = Array();
	// "name", (null | "value") - if value != null then it's a value from this array
	// if value==null then it's from sql-result

	// blocks that we should parse/hide in each item
	var $m_itemBlocks = Array();
	

	var $sMessage = "";

	function CHtmlGrid($db, $name, $html_path)
	{
		$this->CHtmlBlock($name, $html_path);
		$this->m_db = $db;
	}


	function init()
	{
		parent::init();
	}


	// all stuff is here
	function parseBlock(&$html)
	{
		// get sort and dir params
		$sortParam = $this->m_name . "Sort"; // deafult sorting
		$sort = get_param($this->m_name . "Sort", $this->m_sort); // get sort from http
		if ($sort != $this->m_sort)	// if it's not default sorting
			$this->m_dir = "";		// then don't use default dir
		$dir = get_param($this->m_name . "Dir", $this->m_dir); // get dir from http
		if (! isset($this->m_fields[$sort]))	// if there is not such field to sort
			$sort = $this->m_sort;				// then sort by default
		else if ($this->m_fields[$sort][1] != null)	// or if this is not DB field
			$sort = $this->m_sort;					// then sort by default as well
		if ($sort != "" && $dir != "asc" && $dir != "desc") $dir = "asc"; // dir should be 'asc' or 'desc'


		// get the total number of items
		$n_n = $this->m_db->DLookUp($this->m_sqlcount);
		if ($n_n > 0) // if there is some item(s)
		{
			if ($n_n == "") $n_n = 0; // ?

			// number of pages
			$n_p = (int)(($n_n % $this->m_nPerPage > 0 ? 1 : 0) + ($n_n / $this->m_nPerPage));

			$nOffset = get_param($this->m_name . "Offset", ""); // get offset from http
			if ($this->m_lastPageByDefault == 1)	// if we need last page by default
				if ($nOffset === "")					// and if there is not offset yet
					$nOffset = $n_p - 1;			// then switch it to last page
			if ($nOffset < 0) $nOffset = 0;				// 0-first page
			if ($nOffset >= $n_p) $nOffset = $n_p - 1;	// last page


			// first item we are going to view
			if ($this->m_pageMode == 2)
			{
				$nFirst = $nOffset * $this->m_nPerPage;
				if ($nFirst > $n_n - $this->m_nPerPage)
					$nFirst = $n_n - $this->m_nPerPage;
				if ($nFirst < 0)
					$nFirst = 1;
				else
					$nFirst ++;
			} else {
				$nFirst = ($nOffset  * $this->m_nPerPage + 1);
			}
			// last item we are going to view
			if ($this->m_pageMode == 2)
			{
				$nLast = $nFirst + $this->m_nPerPage;
				if ($nLast > $n_n)
					$nLast = $n_n;
			} else {
				$nLast = ($nOffset + 1) * $this->m_nPerPage;
				if ($nLast > $n_n) $nLast = $n_n;
			}

			$html->setvar("info", $nFirst . " - " . $nLast . " всего " . $n_n);

			$sOffset = $this->m_name . "Offset";
			if ($nOffset >= 10)
			{
				$html->setvar("url", $_SERVER["PHP_SELF"] . "?" . correct_param($sOffset, "0"));
				$html->parse($this->m_name . "_first");
				$html->setvar("url", $_SERVER["PHP_SELF"] . "?" . correct_param($sOffset, $nOffset - 10));
				$html->parse($this->m_name . "_prev");
			} else {
				$html->setblockvar($this->m_name . "_first", "");
				$html->setblockvar($this->m_name . "_prev", "");
			}

			if ($html->blockexists($this->m_name . "_page"))
			{
				$n = (int)($nOffset / 10) + 10;
				if ($n > $n_p) $n = $n_p;
				for ($i = (int)($nOffset / 10); $i < $n; $i++)
				{
					$html->setvar("url", $_SERVER["PHP_SELF"] . "?" . correct_param($sOffset, $i));
					$html->setvar("page", $i+1);
					if ($i != $nOffset)
					{
						$html->parse($this->m_name . "_link", false);
						$html->setblockvar($this->m_name . "_curpage", "");
					} else {
						$html->setblockvar($this->m_name . "_link", "");
						$html->parse($this->m_name . "_curpage", false);
					}
					$html->parse($this->m_name . "_page", true);
				}
			}

			if ((int)($nOffset / 10) < (int)($n_p / 10))
			{
				$html->setvar("url", $_SERVER["PHP_SELF"] . "?" . correct_param($sOffset, ($nOffset + 10 > $n_p - 1) ? $n_p - 1 : $nOffset + 10 ));
				$html->parse($this->m_name . "_next");
				$html->setvar("url", $_SERVER["PHP_SELF"] . "?" . correct_param($sOffset, $n_p - 1));
				$html->parse($this->m_name . "_last");
			} else {
				$html->setblockvar($this->m_name . "_next", "");
				$html->setblockvar($this->m_name . "_last", "");
			}
			if ($html->blockexists($this->m_name . "_pager"))
				$html->parse($this->m_name . "_pager");
			$html->setvar("pager2", $html->getvar($this->m_name . "_pager"));
			$html->setblockvar($this->m_name . "_noitems", "");


			$sql = $this->m_sql;

			if ($sort != "")
				$sql .= " ORDER BY " . $sort . " " . $dir;
			$sql .= " LIMIT ";
			if ($nOffset > 0)
			{
				if ($this->m_pageMode == 2) // last page should be filled
				{
					$_i = $nOffset * $this->m_nPerPage;
					if ($_i > $n_n - $this->m_nPerPage)
						$_i = $n_n - $this->m_nPerPage;
					if ($_i > 0)
						$sql .= $_i . ",";
				} else {
					$sql .= ($nOffset * $this->m_nPerPage) . ",";
				}
			}
			$sql .= $this->m_nPerPage;

			if ($this->m_db->query($sql))
			{
				$counter = 0;
				$n = $nLast - $nFirst + 1;
				while($row = $this->m_db->fetch_row())
				{
					foreach ($this->m_fields as $fn => $field)
					{
						if ($field[1] == null)
						{
							$this->m_fields[$fn][2] = $row[$field[0]];
						} else {
							$this->m_fields[$fn][2] = $field[1];
						}
					}
					$this->onItem();
					foreach ($this->m_fields as $field)
					{
						$html->setvar($field[0], $field[2]);
					}
					$html->setvar("n0", $counter);
					$html->setvar("n1", $counter + 1);
					foreach ($this->m_itemBlocks as $itemBlock => $b)
					{
						if ($html->blockexists($this->m_name . "_" . $itemBlock))
						{
							if ($b)
								$html->parse($this->m_name . "_" . $itemBlock, false);
							else
								$html->setblockvar($this->m_name . "_" . $itemBlock, "");
						}
					}
					if ($html->blockexists($this->m_name . "_middle") && $n > 1)
					{
						if ($counter == ceil($n / 2) - 1)
							$html->parse($this->m_name . "_middle", false);
						else
							$html->setblockvar($this->m_name . "_middle", "");
					}
					if ($html->blockexists($this->m_name . "_odd"))
					{
						if ($counter % 2 == 1)
							$html->parse($this->m_name . "_odd", false);
						else
							$html->setblockvar($this->m_name . "_odd", "");
					}
					if ($html->blockexists($this->m_name . "_even"))
					{
						if ($counter % 2 == 0)
							$html->parse($this->m_name . "_even", false);
						else
							$html->setblockvar($this->m_name . "_even", "");
					}
					if ($html->blockexists($this->m_name . "_separator"))
					{
						if ($counter < $n - 1)
							$html->parse($this->m_name . "_separator", false);
						else
							$html->setblockvar($this->m_name . "_separator", "");
					}
					$html->parse($this->m_name . "_item", true);
					$counter ++;

				}

			} else {
				$n_n = 0;
			}

		}
		if ($n_n == 0)
		{
			$html->setvar("info", "");
			$html->setvar($this->m_name . "_pager", "");
			$html->setvar($this->m_name . "_pager2", "");

			if ($html->blockexists($this->m_name . "_noitems"))
				$html->parse($this->m_name . "_noitems");
		}

		foreach ($this->m_fields as $field)
		{
			$b = $this->m_name . "_sort_" . $field[0];
			if ($html->blockexists($b))
			{
				if ($sort == $field[0] && $dir == "asc")
				{
					$html->setvar("url", $_SERVER["PHP_SELF"] . "?" . correct_param2($sortParam, $field[0], $this->m_name . "Dir", "desc"));
				}
				else
					$html->setvar("url", $_SERVER["PHP_SELF"] . "?" . correct_param2($sortParam, $field[0], $this->m_name . "Dir", "asc"));
				if ($sort == $field[0] && $dir == "desc")
					$html->parse($this->m_name . "_desc_" . $field[0]);
				else				
					$html->setvar($this->m_name . "_desc_" . $field[0], "");
				if ($sort == $field[0] && $dir == "asc")
					$html->parse($this->m_name . "_asc_" . $field[0]);
				else				
					$html->setvar($this->m_name . "_asc_" . $field[0], "");
				$html->parse($b);
			}
		}


		if ($this->sMessage != "")
		{
			$html->setvar("sMessage", $this->sMessage);
			if ($html->blockexists($this->m_name . "_bMessage"))
			{
				$html->parse($this->m_name . "_bMessage");
			}
		}

		parent::parseBlock(&$html);
	}


	function onItem()
	{
	}	



	

}

?>
<?php




function HSelectOptions(&$hash, $value)
{
	$opts = "";
	foreach ($hash as $v => $title)
		$opts .= "<option value=\"" . $v . "\"" . (($v == $value) ? " selected" : "") . ">" . $title . "</options>\n";
	return $opts;
}

function NSelectOptions($min, $max, $value)
{
	$opts = "";
	for ($i = $min; $i <= $max; $i++)
		$opts .= "<option value=\"" . $i . "\"" . (($i == $value) ? " selected" : "") . ">" . $i . "</options>\n";
	return $opts;
}


function to_sql($Value, $ValueType)
{
  if($ValueType == "Plain")
  {
    return $Value;
  }

  if(strlen($Value) == 0 && $ValueType != "EmptyStr")
  {
    return "NULL";
  }
  else
  {
    if($ValueType == "Number" || $ValueType == "Float")
    {
      return doubleval(str_replace(",", ".", $Value));
    }
    else if ($ValueType == "Check")
    {
      return ($Value == 1 ? "'Y'" : "'N'");
	}
    else
    {
      return "'" . str_replace("'", "''", $Value) . "'";
    }
  }
}


class CDB
{
/*
	var $sHost = "localhost";
	var $sDB = "findyou9_fyd";
	var $sLogin = "findyou9_fyd";
	var $sPassword = "fyd21";
*/

	var $sHost = "localhost";
	var $sDB = "fyd";
	var $sLogin = "fyd";
	var $sPassword = "fyd21";

	var $conn = 0;
	var $res = 0;

	function connect()
	{
		$this->conn = mysql_connect($this->sHost, $this->sLogin, $this->sPassword);
		if (! $this->conn)
		{
//			die("Can't connect to database: " . mysql_errormsg());
			die("Can't connect to database");
		}
		mysql_select_db($this->sDB) || die("Can't select database");
	}

	function close()
	{
		if ($this->conn)
		{
			mysql_close($this->conn);
			$this->conn = 0;
		}
	}	

	function execute($sql)
	{
		if (! mysql_query($sql, $this->conn))
			return 0;
		return 1;
	}

	function query($sql)
	{
		if ($this->res)
		{
			mysql_free_result($this->res);
			$this->res = 0;
		}
		$this->res = mysql_query($sql, $this->conn);
		if ($this->res)
			return 1;
		else
			return 0;
	}

	function fetch_row()
	{
		if (! $this->res)
			return 0;
		$ret = mysql_fetch_array($this->res);
		if (! $ret)
		{
			mysql_free_result($this->res);
			$this->res = 0;
		}
		return $ret;
	}

    function affected_rows()
	{
		return mysql_affected_rows($this->conn);
	}

	function free_result()
	{
		if ($this->res)
		{
			mysql_free_result($this->res);
			$this->res = 0;
		}
	}

	function get_insert_id()
	{
		return mysql_insert_id($this->conn);
	}


    function DLookUP($sql)
	{
		$ret = 0;
		if ($this->query($sql))
		{
			if ($row = $this->fetch_row())
			{
				$ret = $row[0];
				mysql_free_result($this->res);
				$this->res = 0;
			}
		}
		return $ret;
	}

    function DSelectOptions($sql, $selected)
	{
		$ret = "";
		if ($this->query($sql))
		{
			while ($row = $this->fetch_row())
			{
				$ret .= "<option value=\"" . $row[0] . "\"" . (($row[0] == $selected) ? " selected" : "") . ">" . $row[1] . "</options>\n";
			}
			$this->free_result();
		}
		return $ret;
	}


}

?>
<?php


include_once("include/core.php");
include_once("include/db.php");
include_once("include/html.php");
include_once("include/params.php");
include_once("include/block.php");
include_once("include/record.php");
include_once("include/grid.php");
include_once("include/common.php");
include_once("include/public.php");


$db = new CDB();
$db->connect();
loadOptions($db);

$userId = get_param("userId", "0");
$sCode = get_param("sCode", "");
if ($db->DLookUp("SELECT COUNT(*) FROM users WHERE userId=" . to_sql($userId, "Number") . " AND sCode=" . to_sql($sCode, "")) > 0)
{
	$db->execute("UPDATE users SET bActive='Y' WHERE userId=" . to_sql($userId, "Number"));
	if ($db->query("SELECT bVIP, login,sex,lookSex,cityId FROM users WHERE userId=" . to_sql($userId, "Number")))
	{
		if ($row = $db->fetch_row())
		{
			set_session("_userId", $userId);
			set_session("_login", $row["login"]);
			set_session("_sex", $row["sex"]);
			set_session("_lookSex", $row["lookSex"]);
			set_session("_cityId", $row["cityId"]);
			set_session("_bVIP", $row["bVIP"]);

			header("Location: home.php?mes=registered");
			exit;
		}
	}
}

header("Location: message.php?mes=reg_incorrect");
exit;


?>
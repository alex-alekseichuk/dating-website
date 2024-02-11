<?php

include_once("include/core.php");
include_once("include/db.php");
include_once("include/html.php");
include_once("include/params.php");
include_once("include/block.php");
include_once("include/grid.php");
include_once("include/common.php");
include_once("include/public.php");

if (get_session("_userId") == "" || get_session("_cityId") == "")
{
	header("Location: login.php?mes=login\n");
	exit;
}

$db = new CDB();
$db->connect();
loadOptions($db);

#mysql_query("UPDATE users SET vip_time=(vip_time+1) WHERE inGame>0");

$userId = get_session("_userId");
$cityId = get_session("_cityId");

$cmd = get_param("cmd", "");
if ($cmd == "bank")
{
	$points = get_param("points", "0");
	$points100 = $db->DLookUp("SELECT points100 FROM cities, users WHERE cities.cityId=users.cityId AND userId=" . to_sql($userId, "Number"));
	if ($points100 <= 0)
		$points100 = $g_options["points100"];
	if ($points > 0 && 
		$db->DLookUp("SELECT account FROM users WHERE userId=" . to_sql($userId, "Number"))
			>= $points * $points100 / 100)
	{
		$db->execute("UPDATE users SET account=account-" . to_sql($points * $points100 / 100, "Number") . ", bank=bank+" . to_sql($points, "Number") .
			" WHERE userId=" . to_sql($userId, "Number"));
	}
}
if ($cmd == "game")
{
	$points = get_param("points", "0");
	if ($points > 0 && 
		$db->DLookUp("SELECT bank FROM users WHERE userId=" . to_sql($userId, "Number"))
			>= $points)
	{
		$prevLider = $db->DLookUp("SELECT userId FROM users WHERE inGame>0 AND cityId=" . to_sql($cityId, "Number") . " ORDER BY inGame DESC LIMIT 1");
		$db->execute("UPDATE users SET bank=bank-" . to_sql($points, "Number") . ", inGame=inGame+" . to_sql($points, "Number") .
			" WHERE userId=" . to_sql($userId, "Number"));
		$lider = $db->DLookUp("SELECT userId FROM users WHERE inGame>0 && cityId=" . to_sql($cityId, "Number") . " ORDER BY inGame DESC LIMIT 1");
		if ($userId == $lider && $userId != $prevLider)
		{
			$db->execute("UPDATE liders SET finished=now() WHERE userId=" . to_sql($prevLider, "Number") . " AND cityId=" . to_sql($cityId, "Number"));
			$db->execute("INSERT INTO liders (userId,cityId,started) VALUES (" . to_sql($lider, "Number") . "," . to_sql($cityId, "Number") . ",now())");
		}
	}
}
if ($cmd == "message")
{
	$mes = get_param("mes", "");
	$db->execute("UPDATE users SET sHello=" . to_sql($mes, "") . " WHERE userId=" . to_sql($userId, "Number"));
}


$sql = "SELECT SUM(u.inGame)" .
	" FROM users AS u LEFT JOIN cities as c ON u.cityId=c.cityId" .
	" WHERE u.inGame>0 AND u.cityId=" . to_sql(get_session("_cityId"), "Number") . " AND u.bActive='Y'";

$inGameAll = $db->DLookUp($sql);

$sql = "SELECT u.inGame, u.sHello, u.bVIP, u.userId, login, floor((TO_DAYS(now())-TO_DAYS(birth))/365) as age, c.title as city, lookSex, sex, left(about, 32) as about, picId" .
	" FROM (users AS u LEFT JOIN cities as c ON u.cityId=c.cityId) LEFT JOIN pics as p ON (p.userId=u.userId AND p.bMain='Y' AND p.bApproved='Y')" .
	" WHERE u.inGame>0 AND u.cityId=" . to_sql(get_session("_cityId"), "Number") . " AND u.bActive='Y'" .
	" ORDER BY u.inGame DESC LIMIT " . to_sql($g_options["nLiders"], "Number");

?>

<script language="JavaScript">
<!--

<?

$userIds = Array();

$nLiders = 0;
$db->query($sql);
while ($row = $db->fetch_row())
{
	$userIds[$nLiders] = $row["userId"];
?>
	
parent.userIds[<?=$nLiders?>] = "<?=$row["userId"]?>";
parent.logins[<?=$nLiders?>] = "<?=$row["login"]?>";
parent.ages[<?=$nLiders?>] = "<?=$row["age"]?>";
parent.cities[<?=$nLiders?>] = "<?=$row["city"]?>";
parent.sexs[<?=$nLiders?>] = "<?=$g_sex[$row["sex"]]?>";
parent.lookSexs[<?=$nLiders?>] = "<?=$g_lookSex[$row["lookSex"]]?>";
parent.bVIPs[<?=$nLiders?>] = "<?=$row["bVIP"]?>";
parent.sHellos[<?=$nLiders?>] = "<?=$row["sHello"]?>";
<?
	$inGame = (int)($row["inGame"] * 100 / $inGameAll);
	if ($inGame < 0) $inGame = 0;
	if ($inGame > 100) $inGame = 100;

?>
parent.nRating[<?=$nLiders?>] = "<?=$inGame?>";
parent.nInGame[<?=$nLiders?>] = "<?=$row["inGame"]?>";

<? if ($row["picId"] == "") { ?>
	parent.imgs[<?=$nLiders?>] = "<?=($nLiders == 0 ? "no_l.jpg" : "no_m.jpg")?>";
<? } else { ?>
	parent.imgs[<?=$nLiders?>] = "<?=($row["userId"] . "_" . $row["picId"] . "_" . ($nLiders == 0 ? "s" : "s") . ".jpg")?>";
<? }
	
	
	$nLiders ++;
}
$db->free_result();

?>

parent.nLiders = <?=$nLiders?>;

<?
$db->query("SELECT account,bank,inGame FROM users WHERE userId=" . to_sql(get_session("_userId"), "Number"));
$inGame = 0;
if ($row = $db->fetch_row())
{
	$inGame = $row["inGame"];
?>
parent.account = <?=$row["account"]?>;
parent.bank = <?=$row["bank"]?>;
parent.inGame = <?=$row["inGame"]?>;
<?
}
$db->free_result();

if ($inGame > 0) { ?>
parent.rating = <?=(1 + $db->DLookUp("SELECT COUNT(*) FROM users WHERE userId<>" . to_sql($userId, "Number") . " AND cityId=" . to_sql(get_session("_cityId"), "Number") . " AND inGame>=" . to_sql($inGame, "Number")))?>;
<? } else { ?>
parent.rating = '¬не игры';
<? }

?>

parent.updateGame();

//-->
</script>


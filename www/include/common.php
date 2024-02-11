<?php



//
// these are for all pages of all areas
//


define("PICS_DIR", "/inetpub/wwwroot/fyd/pics/");
define("VIDEO_DIR", "/inetpub/wwwroot/fyd/video/");
define("GEOIP_DIR", "/inetpub/wwwroot/fyd/geoip/");
define("PRESENTS_DIR", "/inetpub/wwwroot/fyd/presents/");
define("IMG_DIR", "/inetpub/wwwroot/fyd/img/");
define("INFO_EMAIL", "info@FindYourDream.ru");
define("SERVER_URL", "http://localhost/fyd/");

/*
define("PICS_DIR", "/home/findyourdream.ru/www/pics/");
define("VIDEO_DIR", "/home/findyourdream.ru/www/video/");
define("GEOIP_DIR", "/home/findyourdream.ru/www/geoip/");
define("PRESENTS_DIR", "/home/findyourdream.ru/www/presents/");
define("IMG_DIR", "/home/findyourdream.ru/www/img/");
define("INFO_EMAIL", "info@FindYourDream.ru");
define("SERVER_URL", "http://FindYourDream.ru/");
*/

define("IMAGE_LOGO", "FindYourDream.ru");
define("IMAGE_LOGO_SIZE", "2"); // 1-5, 0-no text

define("IMAGE_QUALITY", "80");

define("IMAGE_BIG_X", "450");
define("IMAGE_BIG_Y", "600");
define("IMAGE_LIDER_X", "209");
define("IMAGE_LIDER_Y", "281");
//define("IMAGE_MEDIUM_X", "101");
//define("IMAGE_MEDIUM_Y", "164");
define("IMAGE_MEDIUM_X", "164");
define("IMAGE_MEDIUM_Y", "101");
define("IMAGE_SMALL_X", "76");
define("IMAGE_SMALL_Y", "103");

define("PRESENT_BIG_X", "400");
define("PRESENT_BIG_Y", "300");
define("PRESENT_SMALL_X", "120");
define("PRESENT_SMALL_Y", "90");


define("ADMIN_PASSWD", "fyd21");

define("MAX_HELLO", "20");

define("VIP_SALE", "50");

$g_vip = Array ("Y" => "VIP", "N" => "Стандартный");

$g_sex = Array ("M" => "Парень", "F" => "Девушка");
$g_lookSex = Array ("M" => "Парня", "F" => "Девушку");
$g_children = Array ("0" => "Нет", "1" => "1 ребенок", "2" => "2-е детей", "3" => "3-е детей", "4" => "4 детей", "5" => "5 детей", "6" => "более 5-ти детей");

$g_married = Array ("Y" => "Женатый/Замужем", "N" => "Неженатый/Незамужняя");
$g_m_married = Array ("Y" => "Женатый", "N" => "Неженатый");
$g_f_married = Array ("Y" => "Замужем", "N" => "Незамужняя");


$g_liderTypes = Array ("0" => "По просмотрам", "1" => "По письмам", "2" => "Игра лидер");



$g_options = Array();
function loadOptions($db)
{
	global $g_options;

	$sql = "SELECT " .
		"pic," .
		"videoVIP," .
		"liderTypeId," .
		"picSize," .
		"videoSize," .
		"picNum," .
		"videoNum," .
		"nLiders," .
		"gameTimeout," .
		"gamePrice," .
		"points100," .
		"top10," .
		"new10," .
		"view10," .
		"emailPrice," .
		"sendPrice," .
		"ratingUpPrice," .
		"ratingFreezeDays," .
		"presentPrice," .
		"bannerUrl," .
		"bannerId," .
		"voting" .
	" FROM options";

	if ($db->query($sql))
	{
		if ($row = $db->fetch_row())
		{
			$g_options["pic"] = $row["pic"] == "Y" ? 1 : 0;
			$g_options["videoVIP"] = $row["videoVIP"] == "Y" ? 1 : 0;
			$g_options["liderTypeId"] = $row["liderTypeId"];
			$g_options["picSize"] = $row["picSize"];
			$g_options["videoSize"] = $row["videoSize"];
			$g_options["picNum"] = $row["picNum"];
			$g_options["videoNum"] = $row["videoNum"];
			$g_options["nLiders"] = $row["nLiders"];
			$g_options["gameTimeout"] = $row["gameTimeout"];
			$g_options["gamePrice"] = $row["gamePrice"];
			$g_options["points100"] = $row["points100"];
			$g_options["top10"] = $row["top10"];
			$g_options["new10"] = $row["new10"];
			$g_options["view10"] = $row["view10"];
			$g_options["emailPrice"] = $row["emailPrice"];
			$g_options["sendPrice"] = $row["sendPrice"];
			$g_options["ratingUpPrice"] = $row["ratingUpPrice"];
			$g_options["ratingFreezeDays"] = $row["ratingFreezeDays"];
			$g_options["presentPrice"] = $row["presentPrice"];
			$g_options["bannerUrl"] = $row["bannerUrl"];
			$g_options["bannerId"] = $row["bannerId"];
			$g_options["voting"] = $row["voting"];
		}
		$db->free_result();
	}
}



function validateFoto($bMandatory)
{
	global $HTTP_POST_FILES;
	global $g_options;
	$name = "picture";
	$ret = "";
	$exts = Array("gif", "jpg", "jpeg", "png");
	if (isset($HTTP_POST_FILES[$name]) && is_uploaded_file($HTTP_POST_FILES[$name]["tmp_name"]))
	{
		if ($HTTP_POST_FILES[$name]["size"] > $g_options["picSize"] * 1024)
			return "Размер файла фото слишком большой";
		
		$sP = "";
		foreach ($exts as $ext)
		{
			if ($sP != "") $sP .= "|";
			$sP .= "(\." . $ext . ")";
		}
		$sP = "/(" . $sP . ")$/i";

		if (preg_match($sP, $HTTP_POST_FILES[$name]['name']) != 1)
		{
			return "Тип файла фото некорректный";
		}
	} else {
		if ($bMandatory)
			return "Фото обязательно";
	}
		
	return "";
}

function getFotoFileName($userId, $picId)
{
	return $userId . "_" . $picId;
}
function uploadFoto($db, $userId, $picId, $bMain)
{
	global $HTTP_POST_FILES;
	global $g_options;
	$name = "picture";
	$cant = 0;
	if (isset($HTTP_POST_FILES[$name]) && is_uploaded_file($HTTP_POST_FILES[$name]["tmp_name"]))
	{
		if ($bMain == 1)
			$db->execute("UPDATE pics SET bMain='N' WHERE userId=" . to_sql($userId, "Number"));

		if ($picId == "")
		{
			$db->execute("INSERT INTO pics (userId, bMain) VALUES (" . to_sql($userId, "Number") . "," . ($bMain == 1 ? "'Y'" : "'N'") . ")");
			$picId = $db->get_insert_id();
		} else {
			if ($bMain == 1)
				$db->execute("UPDATE pics SET bMain='Y' WHERE userId=" . to_sql($userId, "Number") . " AND picId=" . to_sql($picId, "Number"));
		}

		$sFile_ = PICS_DIR . getFotoFileName($userId, $picId) . "_";
		$im = new Image();

		if ($im->loadImage($HTTP_POST_FILES[$name]['tmp_name']))
		{
			$im->resizeCropped(IMAGE_BIG_X, IMAGE_BIG_Y, IMAGE_LOGO, 0);
			$im->saveImage($sFile_ . "b.jpg", IMAGE_QUALITY);
		} else $cant = 1;
		if ($im->loadImage($HTTP_POST_FILES[$name]['tmp_name']))
		{
			$im->resizeCropped(IMAGE_LIDER_X, IMAGE_LIDER_Y, IMAGE_LOGO, IMAGE_LOGO_SIZE);
			$im->saveImage($sFile_ . "l.jpg", IMAGE_QUALITY);
		} else $cant = 1;
		if ($im->loadImage($HTTP_POST_FILES[$name]['tmp_name']))
		{
			$im->resizeCropped(IMAGE_MEDIUM_X, IMAGE_MEDIUM_Y, IMAGE_LOGO, 0);
			$im->saveImage($sFile_ . "m.jpg", IMAGE_QUALITY);
		} else $cant = 1;
		if ($im->loadImage($HTTP_POST_FILES[$name]['tmp_name']))
		{
			$im->resizeCropped(IMAGE_SMALL_X, IMAGE_SMALL_Y, IMAGE_LOGO, 0);
			$im->saveImage($sFile_ . "s.jpg", IMAGE_QUALITY);
		} else $cant = 1;

	}
}
function deleteFoto($db, $userId, $picId)
{
	$sFile_ = PICS_DIR . getFotoFileName($userId, $picId) . "_";
	@unlink($sFile_ . "b.jpg");
	@unlink($sFile_ . "l.jpg");
	@unlink($sFile_ . "m.jpg");
	@unlink($sFile_ . "s.jpg");
	$db->execute("DELETE FROM pics WHERE userId=" . to_sql($userId, "Number") . " AND picId=" . to_sql($picId, "Number"));
	if ($db->DLookUP("SELECT count(*) FROM pics WHERE bMain='Y' AND userId=" . to_sql($userId, "Number")) == 0)
	{
		$picId = $db->DLookUP("SELECT min(picId) FROM pics WHERE bApproved='Y' AND userId=" . to_sql($userId, "Number"));
		if ($picId > 0)
			$db->execute("UPDATE pics SET bMain='Y' WHERE userId=" . to_sql($userId, "Number") . " AND picId=" . to_sql($picId, "Number"));
	}
}
function deleteFotos($db, $userId)
{
	$db->query("SELECT picId FROM pics WHERE userId=" . to_sql($userId, "Number"));
	while ($row = $db->fetch_row())
	{
		$picId = $row["picId"];
		$sFile_ = PICS_DIR . getFotoFileName($userId, $picId) . "_";
		@unlink($sFile_ . "b.jpg");
		@unlink($sFile_ . "l.jpg");
		@unlink($sFile_ . "m.jpg");
		@unlink($sFile_ . "s.jpg");
	}
	$db->execute("DELETE FROM pics WHERE userId=" . to_sql($userId, "Number"));
}



function validateVideo()
{
	global $HTTP_POST_FILES;
	global $g_options;
	$name = "video";
	$ret = "";
	$exts = Array("avi", "mpeg", "mpg");
	if (isset($HTTP_POST_FILES[$name]) && is_uploaded_file($HTTP_POST_FILES[$name]["tmp_name"]))
	{
		if ($HTTP_POST_FILES[$name]["size"] > $g_options["videoSize"] * 1024 * 1024)
			return "Размер файла видео слишком большой";
		
		$sP = "";
		foreach ($exts as $ext)
		{
			if ($sP != "") $sP .= "|";
			$sP .= "(\." . $ext . ")";
		}
		$sP = "/(" . $sP . ")$/i";

		if (preg_match($sP, $HTTP_POST_FILES[$name]['name']) != 1)
		{
			return "Тип файла видео некорректный";
		}
	} else {
		return "Не удается загрузить файл. Проверьте тип и размер файла.";
	}
	return "";
}

function getVideoFileName($userId, $videoId, $video)
{
	return $userId . "_" . $videoId . "_" . $video;
}
function uploadVideo($db, $userId, $videoId)
{
	global $HTTP_POST_FILES;
	global $g_options;
	$name = "video";
	$cant = 0;
	if (isset($HTTP_POST_FILES[$name]) && is_uploaded_file($HTTP_POST_FILES[$name]["tmp_name"]))
	{
		$video = $HTTP_POST_FILES[$name]["name"];
		if ($videoId == "")
		{
			$db->execute("INSERT INTO videos (userId, video) VALUES (" . to_sql($userId, "Number") . "," . to_sql($video, "") . ")");
			$videoId = $db->get_insert_id();
		}

		$sFile_ = VIDEO_DIR . getVideoFileName($userId, $videoId, $video);
		move_uploaded_file($HTTP_POST_FILES[$name]['tmp_name'], $sFile_);
	}
}
function deleteVideo($db, $userId, $videoId)
{
	$video = $db->DLookUP("SELECT video FROM videos WHERE userId=" . to_sql($userId, "Number") . " AND videoId=" . to_sql($videoId, "Number"));
	$sFile_ = VIDEO_DIR . getVideoFileName($userId, $videoId, $video);
	@unlink($sFile_);
	$db->execute("DELETE FROM videos WHERE userId=" . to_sql($userId, "Number") . " AND videoId=" . to_sql($videoId, "Number"));
}
function deleteVideos($db, $userId)
{
	$db->query("SELECT videoId,video FROM videos WHERE userId=" . to_sql($userId, "Number"));
	while ($row = $db->fetch_row())
	{
		$videoId = $row["videoId"];
		$video = $row["video"];
		$sFile_ = VIDEO_DIR . getVideoFileName($userId, $videoId, $video);
		@unlink($sFile_);
	}
	$db->execute("DELETE FROM videos WHERE userId=" . to_sql($userId, "Number"));
}

?>
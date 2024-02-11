<?

include "include/image.php";

$path = "c:\\2.jpg";
$im = new Image();
if ($im->loadImage($path))
{
	$im->resizeCropped(150, 200, "Лидер", 12);
	$im->saveImage("c:\\2_1.jpg", 80);
}
?>
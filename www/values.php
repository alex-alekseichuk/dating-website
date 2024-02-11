<?
include_once("include/core.php");
include_once("include/db.php");

$db = new CDB();
$db->connect();

$db->EXECUTE("INSERT INTO cities (title,sGeoipCity,priority) VALUES ('Москва','Moscow',1)");
$db->EXECUTE("INSERT INTO cities (title,priority) VALUES ('С.Петербург',2)");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('Казань')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('Владивосток')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('Томск')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('Новосибирск')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('Хабаровск')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('Курск')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('Орел')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('Белгород')");

$db->EXECUTE("INSERT INTO cities (title) VALUES ('Астрахань')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('Мурманск')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('Тюмень')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('Норильск')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('Архангельск')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('Сочи')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('Пятигорск')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('Смоленск')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('Нижн.Новгород')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('Иваново')");

$db->EXECUTE("INSERT INTO cities (title) VALUES ('Ижевск')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('Тула')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('Махачкала')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('Магадан')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('Нижневартовск')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('Нижн.Тагил')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('Волгоград')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('Самара')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('Рязань')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('Севастополь')");


$db->EXECUTE("INSERT INTO options (pic) VALUES ('Y')");

$db->EXECUTE("insert into goals (maskId, title) values (1, 'Sex')");
$db->EXECUTE("insert into intereses (maskId, title) values (1, 'Футбол')");
$db->EXECUTE("insert into langs (maskId, title) values (1, 'Английский')");
$db->EXECUTE("insert into money (title) values ('В материальной поддержке не нуждаюсь')");
$db->EXECUTE("insert into homes (title) values ('Свой дом')");
$db->EXECUTE("insert into alcohols (title) values ('Не употребляю')");
$db->EXECUTE("insert into smokes (title) values ('Не курю')");


?>


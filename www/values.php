<?
include_once("include/core.php");
include_once("include/db.php");

$db = new CDB();
$db->connect();

$db->EXECUTE("INSERT INTO cities (title,sGeoipCity,priority) VALUES ('������','Moscow',1)");
$db->EXECUTE("INSERT INTO cities (title,priority) VALUES ('�.���������',2)");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('������')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('�����������')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('�����')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('�����������')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('���������')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('�����')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('����')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('��������')");

$db->EXECUTE("INSERT INTO cities (title) VALUES ('���������')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('��������')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('������')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('��������')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('�����������')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('����')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('���������')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('��������')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('����.��������')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('�������')");

$db->EXECUTE("INSERT INTO cities (title) VALUES ('������')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('����')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('���������')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('�������')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('�������������')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('����.�����')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('���������')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('������')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('������')");
$db->EXECUTE("INSERT INTO cities (title) VALUES ('�����������')");


$db->EXECUTE("INSERT INTO options (pic) VALUES ('Y')");

$db->EXECUTE("insert into goals (maskId, title) values (1, 'Sex')");
$db->EXECUTE("insert into intereses (maskId, title) values (1, '������')");
$db->EXECUTE("insert into langs (maskId, title) values (1, '����������')");
$db->EXECUTE("insert into money (title) values ('� ������������ ��������� �� ��������')");
$db->EXECUTE("insert into homes (title) values ('���� ���')");
$db->EXECUTE("insert into alcohols (title) values ('�� ����������')");
$db->EXECUTE("insert into smokes (title) values ('�� ����')");


?>


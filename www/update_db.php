<?
include_once("include/core.php");
include_once("include/db.php");

$db = new CDB();
$db->connect();


$db->EXECUTE("alter table users add rating integer unsigned not null default 0");
$db->EXECUTE("alter table users add freeUpDate date");

$db->EXECUTE("alter table options add emailPrice decimal(9,2) not null default 0.5");
$db->EXECUTE("alter table options add sendPrice decimal(9,2) not null default 1.0");
$db->EXECUTE("alter table options add ratingUpPrice decimal(9,2) not null default 1.0");
$db->EXECUTE("alter table options add ratingFreezeDays integer unsigned not null default 5");
$db->EXECUTE("alter table options add presentPrice decimal(9,2) not null default 1.0");
$db->EXECUTE("alter table options add bannerUrl varchar(128)");
$db->EXECUTE("alter table options add bannerId integer unsigned not null default 5");
$db->EXECUTE("alter table options add voting varchar(255)");



$db->EXECUTE("alter table options add create table emails(	userId1 integer unsigned not null, userId2 integer unsigned not null, unique (userId1, userId2))");
$db->EXECUTE("alter table options add create table presents(	presentId integer unsigned primary key not null auto_increment,	title varchar(128) not null,	priority integer unsigned not null default 0)");
$db->EXECUTE("alter table options add create table sentPresents(	sentPresentId integer unsigned primary key not null auto_increment,	userId1 integer unsigned not null, userId2 integer unsigned not null, presentId integer unsigned not null,	bNew char(1) not null default 'Y',	sent datetime not null,	message text)");
$db->EXECUTE("alter table options add create table votes(	voteId integer unsigned primary key not null auto_increment,	title varchar(255) not null,	cnt integer unsigned not null default 0,	priority integer unsigned not null default 0)");


?>

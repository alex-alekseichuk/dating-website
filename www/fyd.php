<?
include_once("include/core.php");
include_once("include/db.php");

$db = new CDB();
$db->connect();


$db->EXECUTE("drop table if exists cities");
$db->EXECUTE("create table cities ( cityId integer unsigned primary key not null auto_increment, 	sGeoipCity varchar(64) null, 	title varchar(64) not null, points100 decimal(9,2) not null default 0, priority integer unsigned not null default 10 )");

$db->EXECUTE("drop table if exists goals");
$db->EXECUTE("create table goals ( 	priority integer unsigned not null default 0,goalId integer unsigned primary key not null auto_increment, 	maskId integer unsigned not null, 	title varchar(64) not null )");

$db->EXECUTE("drop table if exists intereses");
$db->EXECUTE("create table intereses ( priority integer unsigned not null default 0,interesId integer unsigned primary key not null auto_increment, 	maskId integer unsigned not null, 	title varchar(64) not null )");


$db->EXECUTE("drop table if exists langs");
$db->EXECUTE("create table langs ( priority integer unsigned not null default 0,langId integer unsigned primary key not null auto_increment, 	maskId integer unsigned not null, 	title varchar(64) not null )");


$db->EXECUTE("drop table if exists money");
$db->EXECUTE("create table money ( priority integer unsigned not null default 0,moneyId integer unsigned primary key not null auto_increment, 	title varchar(64) not null )");

$db->EXECUTE("drop table if exists homes");
$db->EXECUTE("create table homes ( priority integer unsigned not null default 0,homeId integer unsigned primary key not null auto_increment, 	title varchar(64) not null )");

$db->EXECUTE("drop table if exists alcohols");
$db->EXECUTE("create table alcohols ( priority integer unsigned not null default 0,alcoholId integer unsigned primary key not null auto_increment, 	title varchar(64) not null )");

$db->EXECUTE("drop table if exists smokes");
$db->EXECUTE("create table smokes ( priority integer unsigned not null default 0,smokeId integer unsigned primary key not null auto_increment, 	title varchar(64) not null )");

$db->EXECUTE("drop table if exists users");
$db->EXECUTE("create table users ( 	userId integer unsigned primary key not null auto_increment, 	login varchar(32) not null, 	passwd varchar(32) not null, 	email varchar(80) not null, 	birth date not null, 	sex char(1) not null default 'M', 	lookSex char(1) not null default 'F', 	cityId integer unsigned not null,  	weight integer unsigned, 	hight integer unsigned, 	moneyId integer unsigned, 	childrenId integer unsigned, 	bMarried char(1) not null default 'N', 	homeId integer unsigned, 	alcoholId integer unsigned, 	smokeId integer unsigned, 	goalIds integer unsigned not null default 0, 	interesIds integer unsigned not null default 0, 	langIds integer unsigned not null default 0, 	about text,  	registered datetime not null, 	lastAccess datetime null,  	account decimal(9,2) not null default 0.0, 	bank integer unsigned not null default 0, 	inGame integer unsigned not null default 0, 	sHello varchar(255),  	bVIP char(1) not null default 'N', 	 	bActive char(1) not null default 'N', 	sCode varchar(32) not null,  	nViews integer unsigned not null default 0, 	nMessages integer unsigned not null default 0 		 )");


$db->EXECUTE("drop table if exists pics");
$db->EXECUTE("create table pics ( 	picId integer unsigned primary key not null auto_increment, 	userId integer unsigned not null, 	bMain char(1) not null default 'Y', bApproved char(1) not null default 'Y' )");

$db->EXECUTE("drop table if exists videos");
$db->EXECUTE("create table videos ( 	videoId integer unsigned primary key not null auto_increment, 	video varchar(64), 	userId integer unsigned not null )");

$db->EXECUTE("drop table if exists messages");
$db->EXECUTE("create table messages ( 	messageId integer unsigned primary key not null auto_increment, 	fromId integer unsigned not null, 	userId integer unsigned not null, 	bNew char(1) not null default 'Y', 	sent datetime not null, 	message text )");


$db->EXECUTE("drop table if exists payments");
$db->EXECUTE("create table payments ( 	paymentId integer unsigned primary key not null auto_increment, 	userId integer unsigned not null, 	amount decimal(9,2) not null default 0.0, 	paid datetime not null )");

$db->EXECUTE("drop table if exists stakes");
$db->EXECUTE("create table stakes ( 	stakeId integer unsigned primary key not null auto_increment, 	userId integer unsigned not null, 	amount decimal(9,2) not null default 0.0, 	points integer unsigned not null default 0.0, 	paid datetime not null )");




$db->EXECUTE("drop table if exists options");
$db->EXECUTE("create table options ( 	pic char(1) not null default 'Y', 	liderTypeId integer not null default 0, 	picSize integer unsigned not null default 1024, 	videoSize integer unsigned not null default 2, 	picNum integer unsigned not null default 20, 	videoNum integer unsigned not null default 10, 	nLiders integer unsigned not null default 4, 	gameTimeout integer unsigned not null default 1, 	gamePrice integer unsigned not null default 1, 	points100 decimal(9,2) not null default 1.0,  	top10 integer unsigned not null default 0, 	new10 integer unsigned not null default 5, 	view10 integer unsigned not null default 0, videoVIP char(1) not null default 'Y' )");


$db->EXECUTE("drop table if exists liders");
$db->EXECUTE("create table liders ( 	userId integer unsigned not null, 	cityId integer unsigned not null, 	started datetime not null, 	finished datetime )");



$db->EXECUTE("drop table if exists links");
$db->EXECUTE("create table links (	userId1 integer unsigned not null,	userId2 integer unsigned not null,	linkId integer unsigned not null default 0 )");


?>

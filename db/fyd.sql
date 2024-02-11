-- the script to build fyd database objects
-- runned like: mysql -u fyd -pfyd21 fyd <fyd.sql

-- cities
drop table if exists cities;
create table cities
(
	cityId integer unsigned primary key not null auto_increment,
	sGeoipCity varchar(64) null,
	title varchar(64) not null,
	points100 decimal(9,2) not null default 0, -- price of 100 points specified for the city
	priority integer unsigned not null default 10
);

-- goals of the dating
drop table if exists goals;
create table goals
(
	goalId integer unsigned primary key not null auto_increment,
	maskId integer unsigned not null,
	priority integer unsigned not null default 0,
	title varchar(64) not null
);

-- the set of possible intereses
drop table if exists intereses;
create table intereses
(
	interesId integer unsigned primary key not null auto_increment,
	maskId integer unsigned not null,
	priority integer unsigned not null default 0,
	title varchar(64) not null
);


-- spoken languages
drop table if exists langs;
create table langs
(
	langId integer unsigned primary key not null auto_increment,
	maskId integer unsigned not null,
	priority integer unsigned not null default 0,
	title varchar(64) not null
);

		
-- financial position
drop table if exists money;
create table money
(
	moneyId integer unsigned primary key not null auto_increment,
	priority integer unsigned not null default 0,
	title varchar(64) not null
);

-- residence; where to live
drop table if exists homes;
create table homes
(
	homeId integer unsigned primary key not null auto_increment,
	priority integer unsigned not null default 0,
	title varchar(64) not null
);

-- alcohol status
drop table if exists alcohols;
create table alcohols
(
	alcoholId integer unsigned primary key not null auto_increment,
	priority integer unsigned not null default 0,
	title varchar(64) not null
);

-- smoking status
drop table if exists smokes;
create table smokes
(
	smokeId integer unsigned primary key not null auto_increment,
	priority integer unsigned not null default 0,
	title varchar(64) not null
);





drop table if exists users;
create table users
(
	userId integer unsigned primary key not null auto_increment,
	login varchar(32) not null,
	passwd varchar(32) not null,
	email varchar(80) not null,
	birth date not null,
	sex char(1) not null default 'M', -- M-male, F-female
	lookSex char(1) not null default 'F', -- M-male, F-female
	cityId integer unsigned not null,

	weight integer unsigned,
	hight integer unsigned,
	moneyId integer unsigned,
	childrenId integer unsigned,
	bMarried char(1) not null default 'N',
	homeId integer unsigned,
	alcoholId integer unsigned,
	smokeId integer unsigned,
	goalIds integer unsigned not null default 0,
	interesIds integer unsigned not null default 0,
	langIds integer unsigned not null default 0,
	about text,

	registered datetime not null,
	lastAccess datetime null,

	account decimal(9,2) not null default 0.0, -- of money
	bank integer unsigned not null default 0, -- of points
	inGame integer unsigned not null default 0, -- of points
	sHello varchar(255), -- your message in game

	bVIP char(1) not null default 'N',
	
	bActive char(1) not null default 'N', -- 'Y' if activated
	sCode varchar(32) not null, -- secret code for activation

	nViews integer unsigned not null default 0, -- number of views of user's profile
	nMessages integer unsigned not null default 0, -- number of received messages

	rating integer unsigned not null default 0, -- of points
	freeUpDate date -- last date user up his rating for free
);


drop table if exists pics;
create table pics
(
	picId integer unsigned primary key not null auto_increment,
	userId integer unsigned not null,
	bMain char(1) not null default 'Y',
	bApproved char(1) not null default 'Y' -- 'Y' if activated
);

drop table if exists videos;
create table videos
(
	videoId integer unsigned primary key not null auto_increment,
	video varchar(64),
	userId integer unsigned not null
);


drop table if exists messages;
create table messages
(
	messageId integer unsigned primary key not null auto_increment,
	fromId integer unsigned not null,
	userId integer unsigned not null,
	bNew char(1) not null default 'Y',
	sent datetime not null,
	message text
);


drop table if exists payments;
create table payments
(
	paymentId integer unsigned primary key not null auto_increment,
	userId integer unsigned not null,
	amount decimal(9,2) not null default 0.0, -- of money
	paid datetime not null
);

drop table if exists stakes;
create table stakes
(
	stakeId integer unsigned primary key not null auto_increment,
	userId integer unsigned not null,
	amount decimal(9,2) not null default 0.0, -- of money
	points integer unsigned not null default 0.0, -- of money
	paid datetime not null
);




drop table if exists options;
create table options
(
	pic char(1) not null default 'Y', -- 1-st pic is mandatory
	liderTypeId integer not null default 0, -- see liderTypes
	picSize integer unsigned not null default 1024, -- max kB of uploaded pictures
	videoSize integer unsigned not null default 2, -- max MB of uploaded movies
	picNum integer unsigned not null default 20, -- max number pictures
	videoNum integer unsigned not null default 10, -- max number videos
	nLiders integer unsigned not null default 4, -- number of liders
	gameTimeout integer unsigned not null default 1, -- game period in minutes
	gamePrice integer unsigned not null default 1, -- number of points to take each time
	points100 decimal(9,2) not null default 1.0, -- price of 100 points

	top10 integer unsigned not null default 0, -- number of top10 users on home (by messages)
	new10 integer unsigned not null default 5, -- number of new10 users on home
	view10 integer unsigned not null default 0, -- number of view10 users on home

	videoVIP char(1) not null default 'Y',
	
	emailPrice decimal(9,2) not null default 0.5,			-- price to open one email
	sendPrice decimal(9,2) not null default 1.0,			-- price to send group message
	ratingUpPrice decimal(9,2) not null default 1.0,		-- price to up the rating to 20
	ratingFreezeDays integer unsigned not null default 5,	-- days user can't up rating for free
	presentPrice decimal(9,2) not null default 1.0,			-- price to send picture-present
	bannerUrl varchar(128), -- url of the banner; '' or null means without banner
	bannerId integer unsigned not null default 5,	-- banner ID
	voting varchar(255) -- question for vote; '' or null means without vote

);

-- liders history
drop table if exists liders;
create table liders
(
	userId integer unsigned not null,
	cityId integer unsigned not null,
	started datetime not null,
	finished datetime
);

-- contact or black list items
-- userId1 sent mes to userId2
-- userId1 blocked userId2
-- userId1 received mes from userId2
-- userId1 received group_mes from userId2
drop table if exists links;
create table links
(
	userId1 integer unsigned not null,
	userId2 integer unsigned not null,
	linkId integer unsigned not null default 0 -- 0-contact, 1-black list
);

-- opened emails. one user opens email of another
drop table if exists emails;
create table emails
(
	userId1 integer unsigned not null, -- who open
	userId2 integer unsigned not null, -- email was opened
	unique (userId1, userId2)
);

-- picture-presents
drop table if exists presents;
create table presents
(
	presentId integer unsigned primary key not null auto_increment,
	title varchar(128) not null,
	priority integer unsigned not null default 0
);

-- sent picture-presents
drop table if exists sentPresents;
create table sentPresents
(
	sentPresentId integer unsigned primary key not null auto_increment,
	userId1 integer unsigned not null, -- who sent
	userId2 integer unsigned not null, -- whe received
	presentId integer unsigned not null,
	bNew char(1) not null default 'Y',
	sent datetime not null,
	message text
);

-- questions for vote
drop table if exists votes;
create table votes
(
	voteId integer unsigned primary key not null auto_increment,
	title varchar(255) not null,
	cnt integer unsigned not null default 0,
	priority integer unsigned not null default 0
);


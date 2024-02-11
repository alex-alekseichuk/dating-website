#!/usr/bin/perl

my $s = "-";

use DBI;

#my $db = DBI->connect("dbi:mysql:fyd;host=localhost", "fyd", "fyd21");
my $db = DBI->connect("dbi:mysql:findyou9_fyd;host=localhost", "findyou9_fyd", "fyd21");
my $st;
my $sql;

my $cityId;
my $userId;
my $liderId;
my $gamePrice;


# get options

$sql = "SELECT " .
	"pic," .
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
	"view10" .
" FROM options";
$st = $db->prepare($sql);
$st->execute;
my $options;
if (! ($options = $st->fetchrow_hashref))
{
	exit;
}
$st->finish;





# cut gamePrice for all users who have points in the game

$db->do("UPDATE users SET" .
	" inGame=if(inGame<=" . $options->{gamePrice} . ",".
		"0,inGame-" . $options->{gamePrice} . ")" .
	" WHERE inGame>0"
);

=comment
# get gamePrices for cities
$sql = "SELECT DISTINCT cities.cityId,gamePrice" .
	" FROM cities,users WHERE cities.cityId=users.cityId AND users.inGame>0";
$st = $db->prepare($sql);
$st->execute;
my %gamePrices;
while (my ($cityId, $gamePrice) = $st->fetchrow_array)
{
	$gamePrices{$cityId} = ($gamePrice == 0 ? $options->{gamePrice} : $gamePrice);
}
$st->finish;

# cut gamePrice by each city in the game
foreach $cityId (keys %gamePrices)
{
	$db->do("UPDATE users SET" .
		" inGame=if(inGame<=" . $gamePrices{$cityId} . ",".
			"0,inGame-" . $gamePrices{$cityId} . ")" .
		" WHERE inGame>0 AND cityId=" . $cityId
	);
}
=cut




# close the lider and open new lider if it's

$sql = "SELECT l.cityId, l.userId" .
	" FROM liders AS l JOIN users AS u ON" .
	" u.userId=l.userId " .
	" WHERE l.finished is null AND u.inGame <= 0";
$st = $db->prepare($sql);
$st->execute;
my $liders = $st->fetchall_arrayref;
$st->finish;

foreach my $pLider (@$liders)
{
	$cityId = $pLider->[0];
	$userId = $pLider->[1];

	# finished prev. lider period
	$db->do("UPDATE liders SET finished=now() WHERE userId=" . $userId .
		" AND cityId=" . $cityId .
		" AND finished is null"
	);

	# choose the lider
	$st = $db->prepare("SELECT userId FROM users WHERE cityId=" . $cityId . " AND inGame>0 ORDER BY inGame DESC LIMIT 1");
	$st->execute;
	$liderId = 0;
	($liderId) = $st->fetchrow_aray;
	$st->finish;
	if ($liderId > 0)
	{
		# add new lider
		$db->do("INSERT INTO liders (userId,cityId,started) VALUES (" .
			$userId . "," .
			$cityId . "," .
			"now())");
	}
}



$db->disconnect;



exit;
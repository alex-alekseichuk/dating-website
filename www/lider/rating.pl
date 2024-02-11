#!/usr/bin/perl


use DBI;

#my $db = DBI->connect("dbi:mysql:fyd;host=localhost", "fyd", "fyd21");
my $db = DBI->connect("dbi:mysql:findyou9_fyd;host=localhost", "findyou9_fyd", "fyd21");


$db->do("UPDATE users SET rating=rating-1 WHERE rating>0");

$db->disconnect;


exit;

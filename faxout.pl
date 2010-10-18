#!/usr/bin/perl -w
use strict;
use warnings;
sub random_name_generator($);

# usage: faxout.pl faxHeader localID email dest filename
# example: faxout.pl "My Fax Header" 1213435151 3124348989  myfax.pdf

if ($#ARGV != 4) {
	print qq(FAIL: 5 Arguments needed\n);
	exit(1);
}

my ($faxHeader,$localID,$email,$dest,$filename,$callfile,$callfilename);

$faxHeader = $ARGV[0];
$localID = $ARGV[1];
$email = $ARGV[2];
$dest = $ARGV[3];
$filename = $ARGV[4];

if ($dest) {
	$callfilename = &random_name_generator(12).".call";
	open (MYFILE, ">>/tmp/$callfilename") or die $!;
	# $callfile = "Channel: Local/$callto\@outboundialcontext\n";
        $callfile = "Channel: Local/$callto\@outbound-allroutes\n";
        # $callfile = "Channel: SIP/$callto\n";
        $callfile = $callfile . "MaxRetries: 1\n";
	$callfile = $callfile . "RetryTime: 60\n";
	$callfile = $callfile . "WaitTime: 60\n";
	$callfile = $callfile . "Archive: yes\n";
	$callfile = $callfile . "Context: outboundfax\n";
	$callfile = $callfile . "Extension: s\n";
	$callfile = $callfile . "Priority: 1\n";
        # print qq(Sending file: );
        # print qq($tifname\n);
        # ---------------- settting fax information ----------------
	$callfile = $callfile . "Set: FAXFILE=$filename\n";
        $callfile = $callfile . "Set: FAXHEADER=$faxHeader\n";
        $callfile = $callfile . "Set: LOCALID\n";

        # ----             sending call file now  ------
	print MYFILE $callfile;
	close (MYFILE);
	system("mv /tmp/$callfilename /var/spool/asterisk/outgoing");
}

sub random_name_generator($) {
	my ($namelength, $randomstring, @chars);
	$namelength = shift;
	@chars = ('a'..'z','A'..'Z','0'..'9');
	foreach (1..$namelength) {
		$randomstring .= $chars[rand @chars];
	}
	return $randomstring;
}
# Taken from: http://www.teamforrest.com/blog/156/integrating-fax-for-asterisk/

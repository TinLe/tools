#!/usr/bin/perl
#
# mw.pl - mailwatch... uhh, watches mail, what else?
#	see http://www.homeport.org/~shevett/mailwatch for details.
#
# NOTE - i'm nutty for tabs set to 2 spaces.  deal.
#
# This is my version of the script.
# Tin Le - tin@le.org
# https://github.com/TinLe/tools/postfix/mw.pl
#
# 11/5/05 tin - modified gendist to output 5 rows instead of 2, as the
#	number of messages per hour is much > than 99/hr.
#
# 1/11/98 tin - changed to use Getopt, added -d and -l options for
#	logdir and logfile.  this allows monitoring of multiple maillists.
#
# 2/12/97 des - fixed formatting error in top 'posts by day' line,
#	as well as problems with calculating the average
#	posts per day (it was doing 'em calculated total/6.  eek!)
# 3/31/96 des - Major changes.  There was a bug in the initial scan
#	that may result in miscounted number of posts, due to
#	overwriting an element in a hash.  Fixed.  Reformatted
#	some stuff in the reports, and added an "accounting
#	for" calculation in the 'top' sections.
# 2/19/96 des - zounds.  trailing spaces in the subject lines showed
#	up as different entries in the listing.  fixed.
# 1/19/96 des - changed some problems with very low usage through
#	the logs.  show interval in 'top 10' lists.
# 1/11/96 des - revamped distribution, fixed average problem,
#	added 'period' to limit how much data to parse
# 1/6/96 des - hourly distribution was showing '10's.  fixed.
# 1/3/96 des - unleashed upon the world.

require "timelocal.pl";
#require "getopts.pl";
require "ctime.pl";

use Getopt::Std;

#--------------------------------------------------------------------
# some variables
#

$version="0.34.1";	# Version
$logdir="/var/tmp";	# Directory to place logs
$logfile="mw.log";	# Default log file name.
$listname=$logfile;
$testing=0;		# Set to 1 to print to STDOUT when running (no log)
$threshold=0;		# Stop summaries when down to what value? (0 = all)
$plimit=20;		# maximum number of lines to print per summary
$maxdays=7;		# How many days to summarize posts?
$period=604800;		# How old are the oldest reported posts?  (seconds)
# $period=99999999;	# How old are the oldest reported posts?  (seconds)

#--------------------------------------------------------------------
# some constants
#

$Months="JanFebMarAprMayJunJulAugSepOctNovDec";

#--------------------------------------------------------------------
# u s a g e - show da bums how ta do it.
#

sub usage {
	print STDERR "Usage: $0 -[isdl]\n";
	print STDERR "	i	take input from stdin for a new entry.\n";
	print STDERR "	s	summarize current data.\n";
	print STDERR "	d	directory to place log file.\n";
	print STDERR "	l	full pathname to log file.\n";
	exit(1);
}

#--------------------------------------------------------------------
# c a l c m i d n i g h t - Calculate midnight and return the time
#		integer representing the midnight for the date supplied.
#

sub calcmidnight {
	my($target)=(@_);
	my($m,$d,$y,$nmon);

	($m,$d) = (&ctime($target) =~ /\w\w\w\s+(\w+)\s+(\d+)\s+\d+:\d+:\d+ /);
	($y) = (&ctime(time) =~ /(\d\d\d\d)/);
	$nmon=(index($Months,$m) / 3);
	$y=$y-1900;
	print "Parsed ".&ctime(time)." into $nmon / $d / $y \n" if ($testing);
	$midnight=&timelocal("01","00","00",$d,$nmon,$y);
	return ($midnight);
}	

#--------------------------------------------------------------------
# s n a r f i n p u t -- slurps up stdin, chews it for a bit, and spits
#		it out into the log file in the appropriate form.
#

sub snarfinput {
	while (<>) {
		print "< $_" if ($testing);
		chop;

		if ( /^From / && ! $rfline ) { 
			($rfline) = ($_ =~ /(\w\w\w\s+\d+ \d+:\d+:\d+ \d\d\d\d)/) ; 
			next; 
		}
		if ( /^From: / && ! $fline ) { ($fline) = ($_ =~ /From: (.*)/) ; next; }
		if ( /^Subject: / && ! $sline ) { ($sline) = ($_ =~ /: (.*)/) ; next; }
		if ( /^Date: / && ! $dline ) { ($dline) = ($_ =~ /: (.*)/) ; next; }
		if ( /^$/ ) {
			$lcount=0;
			while (<>) {
				$lcount++;
				$inccount++ if (/^(:|>)/) ;
			}
		}
	}

	#
	# parse up date posted line... 
	# Could be any one of:
	# Sat, 30 Dec 1995 00:33:29 -0500 (EST)
	# Fri, 22 Sep 1995 13:26 -0500 (EST)
	# Thu, 9 Nov 1995 14:50:54 -0400 (GMT-0400)
	# Sat, 30 Dec 95 0:05:27 EST
	# Tue, 03 Oct 1995 13:52:58 -0400
	# Wed, 4 Oct 1995 10:03:20 -0500
	#	Thursday,November 02,1995 5:37PM
	# Thursday, November 02, 1995 3:22PM
	# 95-12-05 21:58:06 EST
	# 24 Dec 1995 22:28:22 EST
	# 16 Oct 95 23:58:57 EDT
	#

	print "Posted: $dline\n" if ($testing);
	($_day,$_tmon,$_year,$_hour,$_min,$_sec) = 
		($dline =~ /(\d+)\s+(\w+)\s+(\d+)\s+(\d+):(\d+)((:\d+| ))/);
	$nmon=(index($Months,$_tmon) / 3);
	$_year=$_year-1900 if ($_year > 1900) ;
	print "dline: s: $_sec, m: $_min, h: $_hour, d: $_day, nm: $nmon, y: $_year\n" if ($testing);
	$postdate=&timelocal($_sec,$_min,$_hour,$_day,$nmon,$_year) ;

	#
	# parse up date received line... (Aug 25 13:05:53 1995)
  # Dec  1 12:41:18 1995
	#

	($_tmon,$_day,$_hour,$_min,$_sec,$_year) = 
		($rfline =~ /(\w\w\w)\s+(\d+)\s+(\d+):(\d+)(:\d+) (\d\d\d\d)/);
	$_sec=0	if (! $_sec);
	$nmon=(index($Months,$_tmon) / 3);
	$_year=$_year-1900;
	print "rfline: s: $_sec, m: $_min, h: $_hour, d: $_day, nm: $nmon, y: $_year\n" if ($testing);
	$recdate=&timelocal($_sec,$_min,$_hour,$_day,$nmon,$_year);

	print "opening logfile $logfile\n" if ($testing);
	open(LOG,">>$logfile") || die "Cannot open $logfile: $!\n";
	print "writing\n" if ($testing);
	print LOG "$postdate:$recdate:$lcount:$inccount:$fline:$sline\n";
	close(LOG);
	print "written! \n" if ($testing);
}

sub numeric {
	$a <=> $b 
}

#--------------------------------------------------------------------
# g e n h i s t o r y - figger out postings for the last coupla
#		days, number per day.
#

sub genhistory {
	my($_dline,$_vline,$m,$d,$y);
	$index=0;
	$hcount=0;
	$pcounter=0;
	$midnight=&calcmidnight(time);
	($w,$m,$d) = (&ctime($midnight) =~ /(\w\w\w)\s+(\w+)\s+(\d+)\s+\d+:\d+:\d+ /);
	$_dline=sprintf("%3s %2d ",$m,$d);
	$_vline="";
	for $i (reverse(sort keys %pdates)) {
		if ($i < $midnight) {
			$_vline="${_vline}".sprintf("  %-3.0f  | ",$pcounter);
			last if ($index == 7);
			$midnight=$midnight - 86400;
			$pcounter=1;
			$hcount++;
			$index++;
			($w,$m,$d) = (&ctime($midnight) =~ /(\w\w\w)\s+(\w+)\s+(\d+)\s+\d+:\d+:\d+ /);
			$_dline="$_dline|".sprintf(" %3s %2d ",$m,$d);
		} else {
			$pcounter++;
			$hcount++;
		}
	}
	$_vline="${_vline}".sprintf("  %-3.0f",$pcounter);
	print "\nBreakdown by day: ($hcount posts, average of ";
	print sprintf("%3.1f",($hcount / 7));
	print " posts per day.)\n";
	print "----------------------------------------------------------------------\n";
	print "\t$_wline\n";
	print "\t$_dline\n";
	print "\t$_vline\n\n";
}

#--------------------------------------------------------------------
# g e n d i s t - figger out the distribution of posting times
#

sub gendist {
	my($output,$i,$hour,@dist);
	for $i (0..23) {@dist[$i]=0};
	for $i (keys %pdates) {
		($hour) = ($pdates{$i} =~ /\w\w\w\s+\w+\s+\d+\s+(\d+):\d+:\d+ /);
		@dist[$hour]++;
	}
	$output="";
	for $i (0..23) {
		$dstring=sprintf("%5s",@dist[$i]);
		$out1="$out1 ".substr($dstring,0,1);
		$out2="$out2 ".substr($dstring,1,1);
		$out3="$out3 ".substr($dstring,2,1);
		$out4="$out4 ".substr($dstring,3,1);
		$out5="$out5 ".substr($dstring,4,1);
	}
	print "\t$out1\n";
	print "\t$out2\n";
	print "\t$out3\n";
	print "\t$out4\n";
	print "\t$out5\n";
	print "\t|---------- AM ---------|---------- PM ---------|\n";
	print "\t 1                     12                      24\n\n";
}

#--------------------------------------------------------------------
# s u m m a r i z e - give em da woiks.  
#

sub summarize {
	local($pdate,$rdate,$poster,$subject);
	local($count,$posters,%pdates,%posters);
	$cutoff=calcmidnight(time - $period + 86400);
	%plist={}; $posterc=0;
	%slist={}; $subjectc=0;
	print "Cutoff is $cutoff ($period seconds ago).\n" if ($testing);
	open(INP,"$logfile") || die "summarize: error opening $logfile: $!\n";
	while(<INP>) {
		chop;
		($pdate,$rdate,$lines,$inclines,$poster,$subject)=split(":",$_,6);
		if ($pdate < $cutoff) {
			print "skipped $pdate by $poster\n" if ($testing);
			next;
		}
		if (! $plist{$poster}) {
			$posterc++;
			$plist{$poster}=$poster;
		}
		if (! $slist{$subject}) {
			$subjectc++;
			$slist{$subject}=$subject
		}
		print "processing $pdate...\n" if ($testing);
		$count++;
		chop($odate=$pdate>$rdate ? &ctime($pdate) : &ctime($rdate)) if (! "$odate");
		$posters{$poster}=$posters{$poster} + 1;
		print "$posters{$poster} - $poster \n" if ($testing);
		$subject=~s/^\[[^:]*: (.*)]$/$1/;
		$subject=~s/^Re: (.*)$/$1/;
		$subject=~s/^(.*)\s+$/$1/;
		$sublist{$subject}++;
		if ($pdates{$pdate}) {
			print "exists!  $poster, $subject\n" if ($testing);
			$pdate="${pdate}.1";
		}
		$pdates{$pdate}=&ctime($pdate);
	}
	chop($ndate=$pdate > $rdate ? &ctime($pdate) : &ctime($rdate));
	close(INP);
	$interval=sprintf("%2.2f",($period / 86400));
	chop($repinv=ctime($cutoff));
	print "$listname Traffic Report                         ".&ctime(time);
	print "======================================================================\n";
	print "Report interval ------: Since $repinv ($interval days)\n";
	print "Oldest post ----------: $odate\n";
	print "Most recent post -----: $ndate \n";
	print "Total posts ----------: $count\n";
	print "Total unique posters -: $posterc\n";
	print "Total unique subjects : $subjectc\n";
	print "======================================================================\n";

	&genhistory();

	for $i (sort keys %posters) {
		$perc = sprintf("%3.1f",($posters{$i} / $count * 100));
		push(@parray,"$posters{$i} ($perc %)\t$i\n");
	}
	$pcounter=0;
	$topcount = 0;
	for $i (reverse(sort numeric @parray)) {
		($num,$text)=split("\t",$i);
		last if (($num == $threshold) && ($threshold));
		last if ($pcounter == $plimit);
		@outarray[$pcounter++]=$i;
		$topcount=$topcount+$num;
	}
	if ($count == 0) {
		$count = 1;
	}
	$ptext=sprintf("%4.1f",($topcount / $count) * 100);
	print "Top $plimit Posters (Representing ${ptext}% of the total traffic.)\n";
	print "----------------------------------------------------------------------\n";
	for $i (@outarray) {
		print "\t$i";
	}


	@parray=();
	for $i (sort keys %sublist) {
		$perc = sprintf("%3.1f",($sublist{$i} / $count * 100));
		push(@sarray,"$sublist{$i} ($perc %)\t$i\n");
	}
	$pcounter=0;
	$topcount=0;
	for $i (reverse(sort numeric @sarray)) {
		($num,$text)=split("\t",$i);
		last if (($num == $threshold) && ($threshold));
		last if ($pcounter == $plimit);
		@outarray[$pcounter++]=$i;
		$topcount=$topcount+$num;
	}
	$ptext=sprintf("%4.1f",($topcount / $count) * 100);
	print "\nTop $plimit subjects (Representing ${ptext}% of the total traffic.)\n";
	print "----------------------------------------------------------------------\n";
	for $i (@outarray) {
		print "\t$i";
	}

	print "\nHourly distribution of postings: \n";
	print "----------------------------------------------------------------------\n";
	&gendist();
	print "======================================================================\n";
#	print "MailWatch v$version by Shayde.            http://www.homeport.org/~shevett\n";
#	print "Modified by Tin Le                   http://tin.le.org/\n";
}

#--------------------------------------------------------------------
# m a i n - the naughty bits...
#

$opt_d = 0;
$opt_l = 0;
$opt_i = 1;
$opt_s = 0;

getopts('sid:l:');

if ($opt_d) {
	$logdir = $opt_d;
}

if ($opt_l) {
	$logfile = $opt_l;
}
$listname = $logfile;
$logfile = $logdir . "/" . $logfile;

if ($opt_s) {
	&summarize;
} elsif ($opt_i) {
	&snarfinput;
}

#!/usr/bin/perl -w

use Getopt::Long;
use Date::Parse;
use File::Basename;

my $progname = "parselog";
my %urls = ();
my ($ts_start, $ts_end) = (0,0);
my @arr = ();
my @logfiles = ();

my ($host, $datacenter) = split(/\./, $ENV{'HOSTNAMEALIAS'});

my $currdate = `date +"%d/%h/%Y"`;
my $currtime = `date +"%H:%M:%S"`;
my $YEAR = `date +"%Y"|tr -d '\r\n'`;
my $totalrecords = 0;

my ($day, $month, $year, $hour, $minute, $sec) = (0) x 6;
my $data;

my $helpflg = 0;

sub Usage {
	print STDERR << "EOF";

	Usage: $progname [--help] logfile
		--help      this help message.
		logfile		logfile to analyze

EOF
	exit;
}

###################
# Main

if (!GetOptions(
		"help|h" => \$helpflg,
)) { Usage(); }

if ($helpflg or !@ARGV) { Usage(); }

for ($i=0; $i<=$#ARGV; $i++) {
  # print "Opening file $ARGV[0] for parsing....\n";
  open(MYFILE, $ARGV[$i]) or die("$progname: Unable to open file $ARGV[$i] for reading: $?\n");
  push(@logfiles, $ARGV[$i]);
  while (<MYFILE>) {
	chomp;

    # we are only interested in timing lines
	next if !(/_timing/);

	$month = $data = '';
#Jan  4 14:38:42 cy87bk1 unified_search_collator[2834]: _timing 9.5 audio 10.0 | omr 9.4 0.004 | melody 0.0 0.000 | speech 0.0 0.000 | api 0.142 | sing -1.000 (0)
	if (m#^(\w+)\s+(\d+)\s+(\d\d):(\d\d):(\d\d)\s+([\w\d]+?)\s+unified_search_collator\[\d+\]: _timing (\d+\.\d+) audio (\d+\.\d+) \| omr (\d+\.\d+) (\d+\.\d+) \| melody (\d+\.\d+) (\d+\.\d+) \| speech (\d+\.\d+) (\d+\.\d+) \| api (\d+\.\d+) \| sing (-?\d+\.\d+) \((\d+)\)\s+$#) {

		$month = $1;
		$day = $2;
		$hour = $3;
		$minute = $4;
		$sec = $5;
		$hostname = $6;
		$timing = $7;
		$audio = $8;
		$omr1 = $9;
		$omr2 = $10;
		$melody1 = $11;
		$melody2 = $12;
		$speech1 = $13;
		$speech2 = $14;
		$api = $15;
		$sing1 = $16;
		$sing2 = $17;

		my $datetime = "$day $month $YEAR $hour:$minute:$sec";
		my $time = (str2time($datetime) ? str2time($datetime): 0);

        printf("timing.%s.%s.melodis.com %f\n", $host, $datacenter, $timing);
        printf("audio.%s.%s.melodis.com %f\n", $host, $datacenter, $audio);
        printf("omr1.%s.%s.melodis.com %f\n", $host, $datacenter, $omr1);
        printf("omr2.%s.%s.melodis.com %f\n", $host, $datacenter, $omr2);
        printf("melody1.%s.%s.melodis.com %f\n", $host, $datacenter, $melody1);
        printf("melody2.%s.%s.melodis.com %f\n", $host, $datacenter, $melody2);
        printf("speech1.%s.%s.melodis.com %f\n", $host, $datacenter, $speech1);
        printf("speech2.%s.%s.melodis.com %f\n", $host, $datacenter, $speech2);
        printf("api.%s.%s.melodis.com %f\n", $host, $datacenter, $api);
        printf("sing1.%s.%s.melodis.com %f\n", $host, $datacenter, $sing1);
        printf("sing2.%s.%s.melodis.com %f\n", $host, $datacenter, $sing2);
	}
  }
  close(MYFILE) or die("Error closing file $ARGV[0]! $?\n");
}

my $startdate = scalar gmtime($ts_start);
my $enddate = scalar gmtime($ts_end);

1;

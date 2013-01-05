#!/usr/bin/perl

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
my @data = {
    _timing => 0.0,
    audio => 0.0,
    omr1 => 0.0,
    omr2 => 0.0,
    melody1 => 0.0,
    melody2 => 0.0,
    speech1 => 0.0,
    speech2 => 0.0,
    api => 0.0,
    sing1 => 0.0,
    sing2 => 0.0
};

my $helpflg = 0;

sub Usage {
  print STDERR << "EOF";

  Usage: $progname [--help] logfile
    --help      this help message.
    logfile    logfile to analyze

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

  $month = '';
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

      $totalrecords++;

      $data{_timing} += $timing;
      $data{audio} += $audio;
      $data{omr1} += $omr1;
      $data{omr2} += $omr2;
      $data{melody1} += $melody1;
      $data{melody2} += $melody2;
      $data{speech1} += $speech1;
      $data{speech2} += $speech2;
      $data{api} += $api;
      $data{sing1} += $sing1;
      $data{sing2} += $sing2;

      # my $datetime = "$day $month $YEAR $hour:$minute:$sec";
      # my $time = (str2time($datetime) ? str2time($datetime): 0);
    }
  }
  close(MYFILE) or die("Error closing file $ARGV[0]! $?\n");

  printf("timing %f\n", $data{_timing}/$totalrecords);
  printf("audio %f\n", $data{audio}/$totalrecords);
  printf("omr1 %f\n", $data{omr1}/$totalrecords);
  printf("omr2 %f\n", $data{omr2}/$totalrecords);
  printf("melody1 %f\n", $data{melody1}/$totalrecords);
  printf("melody2 %f\n", $data{melody2}/$totalrecords);
  printf("speech1 %f\n", $data{speech1}/$totalrecords);
  printf("speech2 %f\n", $data{speech2}/$totalrecords);
  printf("api %f\n", $data{api}/$totalrecords);
  printf("sing1 %f\n", $data{sing1}/$totalrecords);
  printf("sing2 %f\n", $data{sing2}/$totalrecords);
}

1;

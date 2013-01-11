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
my $totalrecords = 1;

my ($day, $month, $year, $hour, $minute, $sec) = (0) x 6;
my @data = {
    _timing => 0.0,
    _timingMin => 0.0,
    _timingMax => 0.0,
    audio => 0.0,
    audioMin => 0.0,
    audioMax => 0.0,
    omr1 => 0.0,
    omr1Min => 0.0,
    omr1Max => 0.0,
    omr2 => 0.0,
    omr2Min => 0.0,
    omr2Max => 0.0,
    melody1 => 0.0,
    melody1Min => 0.0,
    melody1Max => 0.0,
    melody2 => 0.0,
    melody2Min => 0.0,
    melody2Max => 0.0,
    speech1 => 0.0,
    speech1Min => 0.0,
    speech1Max => 0.0,
    speech2 => 0.0,
    speech2Min => 0.0,
    speech2Max => 0.0,
    api => 0.0,
    apiMin => 0.0,
    apiMax => 0.0,
    sing1 => 0.0,
    sing1Min => 0.0,
    sing1Max => 0.0,
    sing2 => 0.0,
    sing2Min => 0.0,
    sing2Max => 0.0
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

      # yuck
      if ($timing > $data{_timingMax}) { $data{_timingMax} = $timing; }
      if ($timing < $data{_timingMin}) { $data{_timingMin} = $timing; }
      if ($audio > $data{audioMax}) { $data{audioMax} = $audio; }
      if ($audio < $data{audioMin}) { $data{audioMin} = $audio; }
      if ($omr1 > $data{omr1Max}) { $data{omr1Max} = $omr1; }
      if ($omr1 < $data{omr1Min}) { $data{omr1Min} = $omr1; }
      if ($omr2 > $data{omr2Max}) { $data{omr2Max} = $omr2; }
      if ($omr2 < $data{omr2Min}) { $data{omr2Min} = $omr2; }
      if ($melody1 > $data{melody1Max}) { $data{melody1Max} = $melody1; }
      if ($melody1 < $data{melody1Min}) { $data{melody1Min} = $melody1; }
      if ($melody2 > $data{melody2Max}) { $data{melody2Max} = $melody2; }
      if ($melody2 < $data{melody2Min}) { $data{melody2Min} = $melody2; }
      if ($speech1 > $data{speech1Max}) { $data{speech1Max} = $speech1; }
      if ($speech1 < $data{speech1Min}) { $data{speech1Min} = $speech1; }
      if ($speech2 > $data{speech2Max}) { $data{speech2Max} = $speech2; }
      if ($speech2 < $data{speech2Min}) { $data{speech2Min} = $speech2; }
      if ($api > $data{apiMax}) { $data{apiMax} = $api; }
      if ($api < $data{apiMin}) { $data{apiMin} = $api; }
      if ($sing1 > $data{sing1Max}) { $data{sing1Max} = $sing1; }
      if ($sing1 < $data{sing1Min}) { $data{sing1Min} = $sing1; }
      if ($sing2 > $data{sing2Max}) { $data{sing2Max} = $sing2; }
      if ($sing2 < $data{sing2Min}) { $data{sing2Min} = $sing2; }

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

  printf("_timingMin %f\n", $data{_timingMin}+0.0);
  printf("_timingMax %f\n", $data{_timingMax}+0.0);
  printf("audioMin %f\n", $data{audioMin}+0.0);
  printf("audioMax %f\n", $data{audioMax}+0.0);
  printf("apiMin %f\n", $data{apiMin}+0.0);
  printf("apiMax %f\n", $data{apiMax}+0.0);
  printf("omr1Min %f\n", $data{omr1Min}+0.0);
  printf("omr1Max %f\n", $data{omr1Max}+0.0);
  printf("omr2Min %f\n", $data{omr2Min}+0.0);
  printf("omr2Max %f\n", $data{omr2Max}+0.0);
  printf("melody1Min %f\n", $data{melody1Min}+0.0);
  printf("melody1Max %f\n", $data{melody1Max}+0.0);
  printf("melody2Min %f\n", $data{melody2Min}+0.0);
  printf("melody2Max %f\n", $data{melody2Max}+0.0);
  printf("speech1Min %f\n", $data{speech1Min}+0.0);
  printf("speech1Max %f\n", $data{speech1Max}+0.0);
  printf("speech2Min %f\n", $data{speech2Min}+0.0);
  printf("speech2Max %f\n", $data{speech2Max}+0.0);
  printf("sing1Min %f\n", $data{sing1Min}+0.0);
  printf("sing1Max %f\n", $data{sing1Max}+0.0);
  printf("sing2Min %f\n", $data{sing2Min}+0.0);
  printf("sing2Max %f\n", $data{sing2Max}+0.0);
}

1;

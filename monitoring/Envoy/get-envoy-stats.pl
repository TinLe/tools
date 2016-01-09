#!/usr/bin/perl

use LWP::UserAgent;
use HTTP::Request;
use HTTP::Response;

my $ENVOYURL='http://192.168.2.95/production';
my $ua = LWP::UserAgent->new(
	agent => "TinsOwn/v1.0 Tin",
);
my $req = HTTP::Request->new(GET => $ENVOYURL);
my $response = $ua->request($req);
my $content = '';

if ($response->is_error()) {
	printf " %s\n", $response->status_line;
} else {
	$content = $response->content();
}

($livesince) = ($content =~ m#live since.+?good>(.+?)</div>#is);
($currently) = ($content =~ m#Currently.+?<td>\s+(\d+\.\d+\s+\w+)</td>#is);
($today) = ($content =~ m#Today.+?<td>\s+(\d+\.\d+\s+\w+)</td>#is);

my $ncurrently = 0.0;
my $ntoday = 0.0;
if ($currently =~ /^(\d+\.?\d+) W/) {
	$ncurrently = ($1 * 1.0);
} elsif ($currently =~ /^(\d+\.?\d+) kW/) {
	$ncurrently = ($1 * 1000.0);
}
if ($today =~ /^(\d+\.?\d+) kW/) {
	$ntoday = ($1 * 1000.0);
} elsif ($today =~ /^(\d+\.?\d+) /) {
	$ntoday = $1 * 1.0;
}
if ($ncurrently <= 0.0) {
	$ntoday = 0.0;
}
printf "%.0f\n", $ncurrently;
printf "%.0f\n", $ntoday;
printf "%s\n", $livesince;
printf "Envoy Solar Power\n";

1;

#<!-- START MAIN PAGE CONTENT -->
#  <h1>System Energy Production</h1>
#    <div style="margin-right: auto; margin-left: auto;"><table>
#      <tr><td colspan="3">System has been live since
#        <div class=good>Tue Jun 01, 2010 03:23 PM PDT</div></td></tr>
#      <tr><td>Currently</td>    <td> 1.06 kW</td></tr><tr><td>Today</td>     <td> 11.1 kWh</td></tr><tr><td>Past Week</td>    <td> 99.5 kWh</td></tr><tr><td>Since Installation</td>    <td> 20.0 MWh</td></tr>
#    </table><br></div>
#<!-- END MAIN PAGE CONTENT -->
#<!-- START BOTTOM NAV CONTENT -->
#  <HR>
#    <div style="margin-left: auto; margin-right:auto; width: 100%; text-align: center; ">
#    &copy; 2007-2012, [e] Enphase Energy, Inc. All rights reserved. |
#    <a href="http://www.enphaseenergy.com/licenses">Licenses</a>
#<!-- END BOTTOM NAV CONTENT -->

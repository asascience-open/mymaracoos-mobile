#!/usr/bin/perl

my @rgb;
use strict;
use Image::Magick;

my $f = $ARGV[0];
my $image  = Image::Magick->new;
$image->Read($f);
my $col    = $image->Get('columns');
my $row    = $image->Get('rows');

for (my $x = 0; $x < $col; $x++) {
  my $cmd = sprintf "convert %s[1x1+%d+0] -format \"%[fx:int(255*r)],%[fx:int(255*g)],%[fx:int(255*b)]\" info:",$f,$x;
  push @rgb,`$cmd`;
}

my $minVal = $ARGV[1];
my $maxVal = $ARGV[2];
my $d      = ($maxVal - $minVal) / $#rgb;
my $cpt    = $ARGV[3];
open F,">$cpt.cpt";
for (my $i = 0; $i <= $#rgb; $i++) {
  my @p = split(/,/,$rgb[$i]);
  printf F "%f\t%d\t%d\t%d\t%f\t%d\t%d\t%d\n",$minVal + $i * $d,@p,$minVal + ($i + 1) * $d,@p;
}
close F;

my $tick = $ARGV[4];
`convert -size 204x34 xc:transparent /tmp/bg.png`;
my $cmd = sprintf "/usr/lib/gmt/bin/makecpt -D -C%s.cpt -T0/10/0.5 > g.cpt ;rm -f .gmt* p.* ; /usr/lib/gmt/bin/gmtset FRAME_PEN 3 BASEMAP_FRAME_RGB 0/0/0 ANNOT_FONT_SIZE_PRIMARY 10 ; /usr/lib/gmt/bin/psscale -D4.628i/1.424i/7.832c/0.5ch -C%s.cpt -B%s > p.ps ; convert -density 400 p.ps -resize 15\% -rotate 90 -trim p.png ; composite -gravity center p.png /tmp/bg.png %s.png ; rm -f .gmt* p.* %s.cpt g.cpt",$cpt,$cpt,$tick,$cpt,$cpt;
`$cmd`;

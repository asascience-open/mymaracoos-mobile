<?php
  $u = $_REQUEST['u'];
  if (!preg_match('/getlegendgraphic/i',$u)) {
    exit;
  }

  $img = '/tmp/'.time().rand().'.png';
  $c = file_get_contents($u);
  file_put_contents($img,$c);
  $origImg = new Imagick($img);
  $origImg->trimImage(0);
  $origImg->borderImage('transparent',10,5);
  $origImg->writeImage($img);
  header('Content-type: image/png');
  $c = file_get_contents($img);
  echo $c;
?>

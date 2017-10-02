<?php
  date_default_timezone_set('UTC');

  header('Content-type:application-json');

  $features = array();

  $json = json_decode(file_get_contents('http://marine.rutgers.edu/cool/auvs/track.php?service=track&region=mab&t0='.urlencode(date("Y-m-d H:i",time() - 365 / 2 * 24 * 3600))),true);
  foreach ($json as $id => $glider) {
    $points = array();
    $times  = array();
    for ($i = 0; $i < count($glider['track']); $i++) {
      array_push($points,$glider['track'][$i]['lon'].' '.$glider['track'][$i]['lat']);
      array_push($times,strtotime($glider['track'][$i]['timestamp']));
    }
    $lastPos = explode(' ',$points[count($points) - 1]);
    if (!isset($activeOnly) || $glider['active'] == 1) {
      array_push($features,array(
         type     => 'Feature'
        ,geometry => array(
           type        => 'Point'
          ,coordinates => array(
             floatval($lastPos[0])
            ,floatval($lastPos[1])
          )
        )
        ,properties  => array(
           minT     => $times[0]
          ,maxT     => $times[count($times) - 1]
          ,track    => 'LINESTRING('.implode(',',$points).')'
          ,url      => $glider['url']
          ,provider => $glider['provider']
          ,type     => $glider['type']
          ,id       => $id
          ,active   => $glider['active'] == 1
        )
      ));
    }
  }

  echo json_encode($features);
?>

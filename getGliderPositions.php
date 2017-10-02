<?php
  date_default_timezone_set('UTC');

  header('Content-type:application/json');

  $features = array();

  $json = json_decode(file_get_contents('https://gliders.ioos.us/map/api/catalog'), true);

  foreach ($json['records'] as $rec) {
    foreach ($rec['children'] as $child) {
      $points = array();
      $times  = array();
      if (
        strtotime($child['end_time']) >= strtotime('3 months ago')
        && array_key_exists('geometry', $child) && array_key_exists('coordinates', $child['geometry'])
      ) {
        for ($i = 0; $i < count($child['geometry']['coordinates']); $i++) {
          array_push($points, $child['geometry']['coordinates'][$i][0].' '.$child['geometry']['coordinates'][$i][1]);
        }

        // Assume positions are ordered oldest to newest.
        $lastPos = explode(' ',$points[count($points) - 1]);

        $type = '';
        if (array_key_exists('extras', $child) && array_key_exists('platform_type', $child['extras'])) {
          $type = $child['extras']['platform_type'];
        }

        $url = '#';
        if (array_key_exists('services', $child)) {
          for ($i = 0; $i < count($child['services']); $i++) {
            if (array_key_exists('protocol', $child['services'][$i]) && $child['services'][$i]['protocol'] == 'thredds') {
              $url = $child['services'][$i]['url'];
            }
          }
        }

        array_push($features,array(
          'type' => 'Feature',
          'geometry' => array(
            'type' => 'Point',
            'coordinates' => array(
              floatval($lastPos[0]),
              floatval($lastPos[1])
            )
          ),
          'properties'  => array(
            'minT' => strtotime($child['end_time']),
            'maxT' => strtotime($child['end_time']),
            'track' => 'LINESTRING('.implode(',', $points).')',
            'url' => $url,
            'provider' => $child['data_provider'],
            'type' => $type,
            'id' => $child['identifier'],
            'active' => strtotime($child['end_time']) >= strtotime('2 days ago')
          )
        ));
      }
    }
  }

  echo json_encode($features);
?>

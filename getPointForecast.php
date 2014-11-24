<?php
  date_default_timezone_set('UTC');

  header('Content-type: application/json');

  $data = array();
  foreach (json_decode(file_get_contents('php://input'),true) as $name => $url) {
    file_put_contents('/tmp/maplog',$url."\n",FILE_APPEND);
    $dataString = file_get_contents($url);
    $xml = @simplexml_load_string($dataString);
    if ($xml && $xml->{'Point'}) {
      foreach ($xml->{'Point'} as $p) {
        // assume that the min time represents all hits
        $a = preg_split("/-| |:/",sprintf("%s",$p->{'Time'}[0]));
        $t = mktime($a[3],$a[4],$a[5],$a[0],$a[1],$a[2]);
        if (isset($_REQUEST['nowOnly'])) {
          if (!array_key_exists('time',$data) || $t < $data['time']) {
            $data['time'] = $t;
          }
        }
        foreach ($p->{'Value'} as $v) {
          $var = sprintf("%s",$v->attributes()->{'Var'});
          $val = sprintf("%s",$v);
          $uom = sprintf("%s",$v->attributes()->{'Unit'});
          if (!array_key_exists($var,$data) && is_numeric($val)) {
            $data[$var] = array(
               'val' => array($val)
              ,'t'   => array($t)
              ,'uom' => $uom
              ,'lyr' => $name
            );
          }
          else if (!isset($_REQUEST['nowOnly']) && is_numeric($val)) {
            array_push($data[$var]['val'],$val);
            array_push($data[$var]['t'],$t);
          }
        }
      }
    }
    else {
      $csv = csv_to_array($dataString);
      for ($i = 0; $i < count($csv); $i++) {
        // assume that the min time represents all hits
        preg_match("/(\d\d\d\d)-(\d\d)-(\d\d)T(\d\d):(\d\d):(\d\d)Z/",$csv[$i]['time'],$a);
        $t = mktime($a[4],$a[5],$a[6],$a[2],$a[3],$a[1]);
        if (isset($_REQUEST['nowOnly'])) {
          if (!array_key_exists('time',$data) || $t < $data['time']) {
            $data['time'] = $t;
          }
        }
        foreach (array_keys($csv[$i]) as $vStr) {
          if ($vStr != 'time') {
            preg_match("/(.*)\[(.*)\]/",$vStr,$a);
            $var = $a[1];
            $val = $csv[$i][$vStr];
            $uom = $a[2];
            if (!array_key_exists($var,$data) && is_numeric($val)) {
              $data[$var] = array(
                 'val' => array($val)
                ,'t'   => array($t)
                ,'uom' => $uom
                ,'lyr' => $name
              );
            }
            else if (!isset($_REQUEST['nowOnly']) && is_numeric($val)) {
              array_push($data[$var]['val'],$val);
              array_push($data[$var]['t'],$t);
            }
          }
        }
      }
    }
  }

  echo json_encode($data);

  // from http://www.php.net/manual/en/function.str-getcsv.php#104558
  function csv_to_array($input,$delimiter=',') {
    $header  = null;
    $data    = array();
    $csvData = str_getcsv($input,"\n");
    foreach ($csvData as $csvLine) {
      if (is_null($header)) {
        $header = explode($delimiter, $csvLine);
      }
      else {
        $items = explode($delimiter, $csvLine);
        for ($n = 0,$m = count($header); $n < $m; $n++) {
          $prepareData[$header[$n]] = $items[$n];
        }
        $data[] = $prepareData;
      }
    }
    return $data;
  }
?>

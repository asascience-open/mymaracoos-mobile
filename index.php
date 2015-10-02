<?php
  date_default_timezone_set('UTC');
  include 'Mobile_Detect.php';
  $detect = new Mobile_Detect();
  $isMobile = $detect->isMobile() || isset($_REQUEST['mobile']);
  $version   = 1.27;
  $olVersion = 0.0;

  $defaults = array(
     'starttime'    => floor(time() / 3600) * 3600 // trunc to hour
    ,'timelength'   => 24 * 2
    ,'timeinterval' => 60 * 3
  );
  // snap it to the nearest timeStepHours
  $dh = date("H",$defaults['starttime']) % ($defaults['timeinterval'] / 60);
  $defaults['starttime'] -= ($dh == 1 ? -1 : $dh == 2 ? 1 : 0) * 3600;
?>

<!DOCTYPE html>
<html>
  <head>
<?php
  if ($isMobile) {
?>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
<?php
  }
?>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <title><?php
  echo isset($_REQUEST['scenario_id']) ? 'OilMap' : 'MyMARACOOS';
  echo $isMobile ? ' Mobile' : ' Lite';
  ?></title>

<?php
  if ($isMobile) {
?>
    <link rel="stylesheet" href="sencha-touch/sencha-touch.css" type="text/css"></link>
    <script src="sencha-touch/sencha-touch.js"></script>
<?php
  }
  else {
?>
    <link rel="stylesheet" type="text/css" href="ext-3.3.0/resources/css/ext-all.css"/>
    <script type="text/javascript" src="ext-3.3.0/adapter/ext/ext-base.js"></script>
    <script type="text/javascript" src="ext-3.3.0/ext-all.js"></script>
<?php
  }
?>

    <script src="OpenLayers/OpenLayers.js?<?php echo $olVersion?>"></script>
    <link rel="stylesheet" href="OpenLayers/theme/default/style.css" type="text/css"></link>

    <script type="text/javascript" src="./jquery/jquery.js"></script>
    <script type="text/javascript" src="./jquery/jquery.flot.js"></script>
    <script type="text/javascript" src="./jquery/jquery.flot.time.js"></script>
    <script type="text/javascript" src="./jquery/jquery.flot.curvedLines.js"></script>

    <script src="dateFormat.js"></script>

    <link rel="stylesheet" href="style.css?<?php echo $version?>" type="text/css"></link>

    <script>
      var timeStepHours = <?php echo json_encode(isset($_REQUEST['timeinterval']) ? $_REQUEST['timeinterval'] / 60 : ($defaults['timeinterval'] / 60))?>;
      var startTime     = <?php echo json_encode(isset($_REQUEST['starttime']) ? strtotime($_REQUEST['starttime'].'Z') : $defaults['starttime'])?>;
      var endTime       = <?php echo json_encode(isset($_REQUEST['timelength']) ? strtotime($_REQUEST['starttime'].'Z') + $_REQUEST['timelength'] * 3600 : ($defaults['starttime'] + $defaults['timelength'] * 3600))?>;
      var utcOffset     = <?php echo json_encode(isset($_REQUEST['utc']) ? $_REQUEST['utc'] : (isset($_REQUEST['scenario_id']) ? '0' : false))?>;
      var scenarioId    = <?php echo json_encode(isset($_REQUEST['scenario_id']) ? $_REQUEST['scenario_id'] : false)?>;
      var initCenter    = <?php echo json_encode(isset($_REQUEST['spillsite']) ? explode(',',$_REQUEST['spillsite']) : false)?>; 
      var queryEnabled = <?php echo json_encode(!isset($_REQUEST['scenario_id']))?>;

      var proj3857   = new OpenLayers.Projection('EPSG:3857');
      var proj4326   = new OpenLayers.Projection('EPSG:4326');
    </script>
    <script src="baselayers.js?<?php echo $version?>"></script>
<?php
  if (isset($_REQUEST['scenario_id'])) {
    echo '<script src="scenario.js.php?'
      .implode('&',array(
         'Oilspill model=on'
        ,'wmsBase='.urlencode(
          'http://'
          .(isset($_REQUEST['server']) ? $_REQUEST['server'] : 'mapappstaging.asascience.com')
          .'/oilmapwebservice20/DrawModel.aspx'
        )
        ,$_SERVER['QUERY_STRING']
        ,$version
      ))
      .'"></script>'."\n";
    $layers = array();
    if (isset($_REQUEST['layers'])) {
      $p0 = explode(',',$_REQUEST['layers']);
      for ($i = 0; $i < count($p0); $i++) {
        $p1 = explode(':',$p0[$i]);
        array_push($layers,$p1[0].'='.$p1[1]);
      }
    }
    echo '<script src="overlays.js.php?'
      .implode('&',array(
         implode('&',$layers)
        ,'wmsBase='.urlencode(
          'http://'
          .(isset($_REQUEST['server']) ? $_REQUEST['server'] : 'mapappstaging.asascience.com')
          .'/oilmapwebservice20/DrawModel.aspx'
        )
        ,$_SERVER['QUERY_STRING']
        ,$version
      ))
      .'"></script>'."\n";
  }
  else {
    echo '<script src="overlays.js.php?'
      .implode('&',array(
         'Winds=on'
        ,'Waves=on'
        ,'Currents=off'
        ,'Currents (regional)=off'
        ,'Currents (NY Harbor)=off'
        ,'Surface water temp=off'
        ,'Bottom water temp=off'
        ,$version
      ))
      .'"></script>'."\n";
    echo '<script src="weather.js?'.$version.'"></script>'."\n";
  }
?>
    <script src="map.js.php<?php
      echo '?isMobile='.json_encode($isMobile)
        .(isset($_REQUEST['scenario_id']) ? '&scenarioId='.$_REQUEST['scenario_id'] : '')
        .'&'.$version
?>"></script>

    <script type="text/javascript">
      var _gaq = _gaq || [];
      _gaq.push(['_setAccount', 'UA-35813089-1']);
      _gaq.push(['_trackPageview']);

      (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
      })();
    </script>
  </head>
  <body <?php 
  if (!$isMobile) {  
    echo 'onload="Ext.onReady(function(){initExtJs()})"';
  }
?>></body>
</html>

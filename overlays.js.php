<?php
  header('Content-type: text/javascript');

  $wmsLayers = array(
     'Winds'    => 'NAM_WINDS'
    ,'Currents' => 'HYCOM_GLOBAL_NAVY_CURRENTS'
  );
  if (isset($_REQUEST['wmslayers'])) {
    $p0 = explode(',',$_REQUEST['wmslayers']);
    for ($i = 0; $i < count($p0); $i++) {
      $p1 = explode(':',$p0[$i]);
      $wmsLayers[$p1[0]] = $p1[1];
    }
  }
?>
var overlays = [
  new OpenLayers.Layer.WMS(
     'Water Level (NECOFS GOM)'
    ,'http://54.174.178.91/wms/NECOFS_GOM3_FORECAST'
    ,{
       layers      : 'sea_surface_height_above_geoid'
      ,transparent : true
      ,styles      : 'contourf_average_seismic_-3.048_3.048_grid_False'
      ,format      : 'image/png'
    }
    ,{
       isBaseLayer      : false
      ,projection       : proj3857
      ,singleTile       : true
      ,wrapDateLine     : true
      ,visibility       : false
      ,initVisibility   : <?php echo json_encode(isset($_REQUEST['Water Level (NECOFS GOM)']) && $_REQUEST['Water Level (NECOFS GOM)'] == 'on')?>
      ,opacity          : 1
      ,noMagic          : true
      ,transitionEffect : 'resize'
      ,timeSensitive    : true
      ,legend           : {
         title : 'Water level (ft)'
        ,image : 'legends/WaterLevel.png'
      }
      ,getFeatureInfo   : {
         extraParams : [
           'LAYERS'
          ,'FORMAT'
          ,'TRANSPARENT'
          ,'STYLES'
        ]
        ,columns     : {
          'sea_surface_height_above_geoid' : {
             name   : 'Water level'
            ,format : function(json) {
              if (isNumber(json['sea_surface_height_above_geoid'].val[0])) {
                return Math.round((json['sea_surface_height_above_geoid'].val[0] * 3.28084) * 10) / 10 + '  ft';
              }
              else {
                return 'forecast unavailable';
              }
            }
          }
        }
      }
      ,charts           : {
        'sea_surface_height_above_geoid' : {
           name   : 'Water level (ft)'
          ,format : function(json,i) {
            if (isNumber(json['sea_surface_height_above_geoid'].val[i])) {
              return [
                 new Date(json['sea_surface_height_above_geoid'].t[i] * 1000)
                ,Number(json['sea_surface_height_above_geoid'].val[i] * 3.28084)
                ,null
              ];
            }
            else {
              return [
                 null
                ,null
                ,null
              ];
            }
          }
          ,nowVal : function(json,i) {
            return Number(json['sea_surface_height_above_geoid'].val[i] * 3.28084);
          }
        }
      }
    }
  )
  ,new OpenLayers.Layer.WMS(
     'Waves'
    ,'http://coastmap.com/ecop/wms.aspx'
    ,{
       layers      : 'WW3_WAVE_HEIGHT'
      ,transparent : true
      ,styles      : ''
      ,format      : 'image/png'
    }
    ,{
       isBaseLayer      : false
      ,projection       : proj3857
      ,singleTile       : true
      ,wrapDateLine     : true
      ,visibility       : false
      ,initVisibility   : <?php echo json_encode(isset($_REQUEST['Waves']) && $_REQUEST['Waves'] == 'on')?>
      ,opacity          : 1
      ,noMagic          : true
      ,transitionEffect : 'resize'
      ,timeSensitive    : true
      ,legend           : {
         title : 'Wave height (ft)'
        ,image : 'legends/WaveHeight.png'
      }
      ,getFeatureInfo   : {
         extraParams : [
           'LAYERS'
          ,'FORMAT'
          ,'TRANSPARENT'
          ,'STYLES'
        ]
        ,columns     : {
          'Height of Combined Wind, Waves and Swells' : {
             name   : 'Waves'
            ,format : function(json) {
              if (isNumber(json['Height of Combined Wind, Waves and Swells'].val[0])) { 
                return Math.round(json['Height of Combined Wind, Waves and Swells'].val[0] * 10 * 3.28084) / 10 + '\'';
              }
              else {
                return 'forecast unavailable';
              }
            }
          }
        }
      }
      ,charts           : {
        'Height of Combined Wind, Waves and Swells' : {
           name   : 'Wave height (ft)'
          ,format : function(json,i) {
            if (isNumber(json['Height of Combined Wind, Waves and Swells'].val[i])) {
              return [
                 new Date(json['Height of Combined Wind, Waves and Swells'].t[i] * 1000)
                ,Number(json['Height of Combined Wind, Waves and Swells'].val[i] * 3.28084)
                ,null
              ];
            }
            else {
              return [
                 null
                ,null
                ,null
              ];
            }
          }
          ,nowVal : function(json,i) {
            return Number(json['Height of Combined Wind, Waves and Swells'].val[i] * 3.28084)
          }
        }
      }
    }
  )
  ,new OpenLayers.Layer.WMS(
     'Bottom water temp'
    ,'http://wms.maracoos.org/wms/maracoos_espresso/'
    ,{
       layers      : 'temp'
      ,transparent : true
      ,styles      : 'pcolor_average_jet_5_20_node_False'
      ,format      : 'image/png'
      ,elevation   : '0'
    }
    ,{
       isBaseLayer      : false
      ,projection       : proj3857
      ,singleTile       : true
      ,wrapDateLine     : true
      ,visibility       : false
      ,initVisibility   : <?php echo json_encode(isset($_REQUEST['Bottom_water_temp']) && $_REQUEST['Bottom_water_temp'] == 'on')?>
      ,opacity          : 1
      ,noMagic          : true
      ,transitionEffect : 'resize'
      ,timeSensitive    : true
      ,legend           : {
         title : 'Bottom water temp (&deg;F)'
        ,image : 'legends/BottomWaterTemp.png'
      }
      ,getFeatureInfo   : {
         extraParams : [
           'STYLES'
          ,'ELEVATION'
        ]
        ,columns     : {
          'temp' : {
             name   : 'Bottom water temp'
            ,format : function(json) {
              if (isNumber(json['temp'].val[0])) { 
                return Math.round((json['temp'].val[0] * 9/5 + 32) * 10) / 10 + '  deg F';
              }
              else {
                return 'forecast unavailable';
              }
            }
          }
        }
      }
      ,charts           : {
        'temp' : {
           name   : 'Bottom water temp (deg F)'
          ,format : function(json,i) {
            if (isNumber(json['temp'].val[i])) {
              return [
                 new Date(json['temp'].t[i] * 1000)
                ,Number(json['temp'].val[i] * 9/5 + 32)
                ,null
              ];
            }
            else {
              return [
                 null
                ,null
                ,null
              ];
            }
          }
          ,nowVal : function(json,i) {
            return Number(json['temp'].val[i] * 9/5 + 32);
          }
        }
      }
    }
  )
  ,new OpenLayers.Layer.WMS(
     'Bottom water temp contours'
    ,'http://ec2-107-21-136-52.compute-1.amazonaws.com:8080/wms/necofs_forecast/?ELEVATION=39&'
    ,{
       layers      : 'temp'
      ,transparent : true
      ,styles      : 'contours_average_gray_5_20_node_False'
      ,format      : 'image/png'
    }
    ,{
       isBaseLayer      : false
      ,projection       : proj3857
      ,singleTile       : true
      ,wrapDateLine     : true
      ,visibility       : false
      ,initVisibility   : <?php echo json_encode(isset($_REQUEST['Bottom_water_temp_contours']) && $_REQUEST['Bottom_water_temp_contours'] == 'on')?>
      ,opacity          : 1
      ,noMagic          : true
      ,transitionEffect : 'resize'
      ,timeSensitive    : true
    }
  )
  ,new OpenLayers.Layer.WMS(
     'Surface water temp'
    ,'http://coastmap.com/ecop/wms.aspx'
    ,{
       layers      : 'HYCOM_GLOBAL_NAVY_SST'
      ,transparent : true
      ,styles      : ''
      ,format      : 'image/png'
    }
    ,{
       isBaseLayer      : false
      ,projection       : proj3857
      ,singleTile       : true
      ,wrapDateLine     : true
      ,visibility       : false
      ,initVisibility   : <?php echo json_encode(isset($_REQUEST['Surface_water_temp']) && $_REQUEST['Surface_water_temp'] == 'on')?>
      ,opacity          : 1
      ,noMagic          : true
      ,transitionEffect : 'resize'
      ,timeSensitive    : true
      ,legend           : {
         title : 'Surface water temp (&deg;F)'
        ,image : 'legends/SurfaceWaterTemp.png'
      }
      ,getFeatureInfo   : {
         extraParams : [
           'LAYERS'
          ,'FORMAT'
          ,'TRANSPARENT'
          ,'STYLES'
        ]
        ,columns     : {
          'Water Temperature' : {
             name   : 'Surface water temp'
            ,format : function(json) {
              if (isNumber(json['Water Temperature'].val[0])) {
                return Math.round((json['Water Temperature'].val[0] * 9/5 + 32) * 10) / 10 + '  deg F';
              }
              else {
                return 'forecast unavailable';
              }
            }
          }
        }
      }
      ,charts           : {
        'Water Temperature' : {
           name   : 'Surface water temp (deg F)'
          ,format : function(json,i) {
            if (isNumber(json['Water Temperature'].val[i])) {
              return [
                 new Date(json['Water Temperature'].t[i] * 1000)
                ,Number(json['Water Temperature'].val[i] * 9/5 + 32)
                ,null
              ];
            }
            else {
              return [
                 null
                ,null
                ,null
              ];
            }
          }
          ,nowVal : function(json,i) {
            return Number(json['Water Temperature'].val[i] * 9/5 + 32);
          }
        }
      }
    }
  )
  ,new OpenLayers.Layer.WMS(
     'Winds'
    ,<?php
      echo json_encode((isset($_REQUEST['scenario_id']) && $wmsLayers['Winds'] == 'wind') ? $_REQUEST['wmsBase'] : 'http://coastmap.com/ecop/wms.aspx')
    ?>
    ,{
       layers      : <?php echo json_encode($wmsLayers['Winds'])?>
      ,transparent : true
      ,format      : 'image/png'
<?php
  if (isset($_REQUEST['scenario_id']) && $wmsLayers['Winds'] == 'wind') {
    echo <<< EOJS
      ,styles        : ''
      ,LANG          : 1
      ,OM_CONTOUR    : false
      ,OM_MASS       : false
      ,OM_BOOM       : false
      ,DAYNIGHTICON  : false
      ,OM_OVERFLIGHT : false
      ,SUMMARYTABLE  : false
      ,OM_SWEPT      : false
      ,OM_SPILLETS   : false
      ,OM_TRACKLINE  : false
      ,flag          : '$_REQUEST[flag]'
      ,scenario_id   : '$_REQUEST[scenario_id]'
EOJS;
  }
  else {
    echo ",styles : 'WINDS_VERY_SPARSE_GRADIENT-False-0.33-0-45-High'";
  }
?>
    }
    ,{
       isBaseLayer      : false
      ,projection       : proj3857
      ,singleTile       : true
      ,wrapDateLine     : true
      ,visibility       : false
      ,initVisibility   : <?php echo json_encode(isset($_REQUEST['Winds']) && $_REQUEST['Winds'] == 'on')?>
      ,opacity          : 1
      ,noMagic          : true
      ,transitionEffect : 'resize'
      ,timeSensitive    : true
<?php
  if ($wmsLayers['Winds'] != 'wind') {
?>
      ,makeStyle        : function(z) {
        var s = {
           '3' : 5
          ,'4' : 4.5
          ,'5' : 4.5
          ,'6' : 3
          ,'7' : 1.25
          ,'8' : 0.5
          ,'9' : 0.25
        };
        return 'WINDS_VERY_SPARSE_GRADIENT-False-' + (z < 3 ? s['3'] : z > 9 ? s['9'] : s[z]) + '-0-45-High';
      }
<?php
  }
?>
<?php
  if (!isset($_REQUEST['scenario_id'])) {
?>
      ,legend           : {
         title : 'Wind speed (kt)'
        ,image : 'legends/WindSpeed.png'
      }
<?php
  }
  else {
?>
      ,legend           : {
        image : 'getLegendGraphic'
      }
<?php
  }
?>
      ,getFeatureInfo   : {
         extraParams : [
           'LAYERS'
          ,'FORMAT'
          ,'TRANSPARENT'
          ,'STYLES'
        ]
        ,columns     : {
          'Wind Velocity' : {
             name   : 'Winds'
            ,format : function(json) {
              if (isNumber(json['Wind Velocity'].val[0]) && isNumber(json['Direction'].val[0])) {
                return Math.round(json['Wind Velocity'].val[0]) + ' kt from the ' + degreesToCompass(Number(json['Direction'].val[0]) + 180);
              }
              else {
                return 'forecast unavailable';
              }
            }
          }
        }
      }
      ,charts           : {
        'Wind Velocity' : {
           name   : 'Wind speed and direction (kt)'
          ,format : function(json,i) {
            if (isNumber(json['Wind Velocity'].val[i]) && isNumber(json['Direction'].val[i])) {
              return [
                 new Date(json['Wind Velocity'].t[i] * 1000)
                ,Number(json['Wind Velocity'].val[i])
                ,Number(json['Direction'].val[i])
              ];
            }
            else {
              return [
                 null
                ,null
                ,null
              ];
            }
          }
          ,nowVal : function(json,i) {
            return Number(json['Wind Velocity'].val[i])
          }
        }
      }
    }
  )
  ,new OpenLayers.Layer.WMS(
     'Currents'
    ,<?php
      echo json_encode((isset($_REQUEST['scenario_id']) && $wmsLayers['Currents'] == 'current') ? $_REQUEST['wmsBase'] : 'http://coastmap.com/ecop/wms.aspx')
    ?>
    ,{
       layers      : <?php echo json_encode($wmsLayers['Currents'])?>
      ,transparent : true
      ,styles      : ''
      ,format      : 'image/png'
<?php
  if (isset($_REQUEST['scenario_id']) && $wmsLayers['Currents'] == 'current') {
    echo <<< EOJS
      ,styles        : ''
      ,LANG          : 1
      ,OM_CONTOUR    : false
      ,OM_MASS       : false
      ,OM_BOOM       : false
      ,DAYNIGHTICON  : false
      ,OM_OVERFLIGHT : false
      ,SUMMARYTABLE  : false
      ,OM_SWEPT      : false
      ,OM_SPILLETS   : false
      ,OM_TRACKLINE  : false
      ,flag          : '$_REQUEST[flag]'
      ,scenario_id   : '$_REQUEST[scenario_id]'
EOJS;
  }
?>
    }
    ,{
       isBaseLayer      : false
      ,projection       : proj3857
      ,singleTile       : true
      ,wrapDateLine     : true
      ,visibility       : false
      ,initVisibility   : <?php echo json_encode(isset($_REQUEST['Currents']) && $_REQUEST['Currents'] == 'on')?>
      ,opacity          : 1
      ,noMagic          : true
      ,transitionEffect : 'resize'
      ,timeSensitive    : true
<?php
  if (!isset($_REQUEST['scenario_id'])) {
?>
      ,legend           : {
         title : 'Current speed (kt)'
        ,image : 'legends/CurrentSpeed.png'
      }
<?php
  }
  else {
?>
      ,legend           : {
        image : 'getLegendGraphic'
      }
<?php
  }
?>
      ,getFeatureInfo   : {
         extraParams : [
           'LAYERS'
          ,'FORMAT'
          ,'TRANSPARENT'
          ,'STYLES'
        ]
        ,columns     : {
          'Water Velocity' : {
             name   : 'Currents'
            ,format : function(json) {
              if (isNumber(json['Water Velocity'].val[0]) && isNumber(json['Direction'].val[0])) {
                return Math.round(json['Water Velocity'].val[0] * 10) / 10 + ' kt to the ' + degreesToCompass(json['Direction'].val[0]);
              }
              else {
                return 'forecast unavailable';
              }
            }
          }
        }
      }
      ,charts           : {
        'Water Velocity' : {
           name   : 'Current speed and direction (kt)'
          ,format : function(json,i) {
            if (isNumber(json['Water Velocity'].val[i]) && isNumber(json['Direction'].val[i])) {
              return [
                 new Date(json['Water Velocity'].t[i] * 1000)
                ,Number(json['Water Velocity'].val[i])
                ,Number(json['Direction'].val[i])
              ];
            }
            else {
              return [
                 null
                ,null
                ,null
              ];
            }
          }
          ,nowVal : function(json,i) {
            return Number(json['Water Velocity'].val[i])
          }
        }
      }
    }
  )
  ,new OpenLayers.Layer.WMS(
     'Currents (regional)'
    ,'http://coastmap.com/ecop/wms.aspx'
    ,{
       layers      : 'ESPRESSO_CURRENTS'
      ,transparent : true
      ,styles      : ''
      ,format      : 'image/png'
    }
    ,{
       isBaseLayer      : false
      ,projection       : proj3857
      ,singleTile       : true
      ,wrapDateLine     : true
      ,visibility       : false
      ,initVisibility   : <?php echo json_encode(isset($_REQUEST['Currents (regional)']) && $_REQUEST['Currents (regional)'] == 'on')?>
      ,opacity          : 1
      ,noMagic          : true
      ,transitionEffect : 'resize'
      ,timeSensitive    : true
      ,legend           : {
         title : 'Current speed (kt)'
        ,image : 'legends/CurrentSpeed.png'
      }
      ,getFeatureInfo   : {
         extraParams : [
           'LAYERS'
          ,'FORMAT'
          ,'TRANSPARENT'
          ,'STYLES'
        ]
        ,columns     : {
          'Water Velocity' : {
             name   : 'Currents (regional)'
            ,format : function(json) {
              if (isNumber(json['Water Velocity'].val[0]) && isNumber(json['Direction'].val[0])) {
                return Math.round(json['Water Velocity'].val[0] * 10) / 10 + ' kt to the ' + degreesToCompass(json['Direction'].val[0]);
              }
              else {
                return 'forecast unavailable';
              }
            }
          }
        }
      }
      ,charts           : {
        'Water Velocity' : {
           name   : 'Current speed and direction (kt)'
          ,format : function(json,i) {
            if (isNumber(json['Water Velocity'].val[i]) && isNumber(json['Direction'].val[i])) {
              return [
                 new Date(json['Water Velocity'].t[i] * 1000)
                ,Number(json['Water Velocity'].val[i])
                ,Number(json['Direction'].val[i])
              ];
            }
            else {
              return [
                 null
                ,null
                ,null
              ];
            }
          }
          ,nowVal : function(json,i) {
            return Number(json['Water Velocity'].val[i])
          }
        }
      }
    }
  )
  ,new OpenLayers.Layer.WMS(
     'Currents (NY Harbor)'
    ,'http://coastmap.com/ecop/wms.aspx'
    ,{
       layers      : 'NYHOPSCUR_currents'
      ,transparent : true
      ,styles      : ''
      ,format      : 'image/png'
    }
    ,{
       isBaseLayer      : false
      ,projection       : proj3857
      ,singleTile       : true
      ,wrapDateLine     : true
      ,visibility       : false
      ,initVisibility   : <?php echo json_encode(isset($_REQUEST['Currents (NY Harbor)']) && $_REQUEST['Currents (NY Harbor)'] == 'on')?>
      ,opacity          : 1
      ,noMagic          : true
      ,transitionEffect : 'resize'
      ,timeSensitive    : true
      ,legend           : {
         title : 'Current speed (kt)'
        ,image : 'legends/CurrentSpeed.png'
      }
      ,getFeatureInfo   : {
         extraParams : [
           'LAYERS'
          ,'FORMAT'
          ,'TRANSPARENT'
          ,'STYLES'
        ]
        ,columns     : {
          'Water Velocity' : {
             name   : 'Currents (NY Harbor)'
            ,format : function(json) {
              if (isNumber(json['Water Velocity'].val[0]) && isNumber(json['Direction'].val[0])) {
                return Math.round(json['Water Velocity'].val[0] * 10) / 10 + ' kt to the ' + degreesToCompass(json['Direction'].val[0]);
              }
              else {
                return 'forecast unavailable';
              }
            }
          }
        }
      }
      ,charts           : {
        'Water Velocity' : {
           name   : 'Current speed and direction (kt)'
          ,format : function(json,i) {
            if (isNumber(json['Water Velocity'].val[i]) && isNumber(json['Direction'].val[i])) {
              return [
                 new Date(json['Water Velocity'].t[i] * 1000)
                ,Number(json['Water Velocity'].val[i])
                ,Number(json['Direction'].val[i])
              ];
            }
            else {
              return [
                 null
                ,null
                ,null
              ];
            }
          }
          ,nowVal : function(json,i) {
            return Number(json['Water Velocity'].val[i])
          }
        }
      }
    }
  )
];

var forecastOrder = [];

<?php
  $a = array();
  $l = array(
     'Winds'
    ,'Waves'
    ,'Water Level (NECOFS GOM)'
    ,'Currents'
    ,'Currents (regional)'
    ,'Currents (NY Harbor)'
    ,'Surface water temp'
    ,'Bottom water temp'
  );
  for ($i = 0; $i < count($l); $i++) {
    if (isset($_REQUEST[str_replace(' ','_',$l[$i])])) {
      array_push($a,$l[$i]);
    }
  }
  if (isset($_REQUEST['scenario_id'])) {
    echo 'weatherOrder = weatherOrder.concat('.json_encode($a).');';
  }
  else {
    echo 'var forecastOrder = '.json_encode($a).';';
  }
?>

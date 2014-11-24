<?php
  header('Content-type: text/javascript');
?>
var weather = [
  new OpenLayers.Layer.WMS(
     'Oilspill model'
    ,<?php echo json_encode($_REQUEST['wmsBase'])?>
    ,{
       layers        : 'model'
      ,transparent   : true
      ,styles        : ''
      ,format        : 'image/png'
      ,LANG          : 1
      ,OM_CONTOUR    : true
      ,OM_MASS       : true
      ,OM_BOOM       : false
      ,DAYNIGHTICON  : false
      ,OM_OVERFLIGHT : false
      ,SUMMARYTABLE  : false
      ,OM_SWEPT      : false
      ,OM_SPILLETS   : false
      ,OM_TRACKLINE  : true
      ,flag          : <?php echo json_encode($_REQUEST['flag'])?>
      ,scenario_id   : <?php echo json_encode($_REQUEST['scenario_id'])?>
    }
    ,{
       isBaseLayer      : false
      ,projection       : proj3857
      ,singleTile       : true
      ,wrapDateLine     : true
      ,visibility       : false
      ,initVisibility   : <?php echo json_encode(isset($_REQUEST['Oilspill_model']) && $_REQUEST['Oilspill_model'] == 'on')?>
      ,opacity          : 1
      ,noMagic          : true
      ,transitionEffect : 'resize'
      ,timeSensitive    : true
      ,getFeatureInfo   : false
      ,customParams     : {
         'Particles'          : ['OM_SPILLETS']
        ,'Thickness contours' : ['OM_CONTOUR','OM_MASS']
      }
      ,legend           : {
        image : 'getLegendGraphic'
      }
    }
  )
];

var weatherOrder = [
   'Oilspill model'
];

<?php
  header('Content-type: text/javascript');
  $isMobile = $_REQUEST['isMobile'] == 'true';
?>
var map;
var glidersCtl;
var loadingOverlays = {};
var initExtent = new OpenLayers.Bounds(-77,35,-69,42).transform(proj4326,proj3857);

var animateTimeout = 1500;
var animating      = false;
var animateTimer;

var dNow   = resetTime();
var sliderTooltip;
var charts = {};

var observationOrder = <?php
  echo json_encode(!isset($_REQUEST['scenarioId']) ? array('Gliders') : false);
?>;

var mapToolbarItems = [];
if (startTime == 'now') {
  mapToolbarItems = [
     {iconCls : 'arrow_left',handler : function() {goPrevTimeStep()}}
    ,{id : 'playPauseButton',iconCls : 'play',handler : function() {
       this.setIconCls(!animating ? 'pause' : 'play');
       playPause(!animating);
    }}
    ,{iconCls : 'arrow_right',handler : function() {goNextTimeStep(true)}}
    ,{xtype : 'spacer'}
    ,{iconCls : 'layers',handler : function() {selectMapOptions()}}
  ];
}
else {
  mapToolbarItems = [
/*
    {id : 'playPauseButton',width : '20%',maxWidth : 50,iconCls : 'play',handler : function() {
       this.setIconCls(!animating ? 'pause' : 'play');
       playPause(!animating);
    }}
*/
     {text : dateToAbbrevString(new Date(startTime * 1000)),handler : function() {
      var d = new Date(startTime * 1000);
      setMapTime(d);
      if (Ext.getCmp('timeSlider')) {
        Ext.getCmp('timeSlider').setValue((d.getTime() - new Date(startTime * 1000).getTime()) / (timeStepHours * 3600 * 1000));
      }
    }}
    ,{
       xtype    : 'sliderfield'
      ,cls      : 'slider'
      ,id       : 'timeSlider'
      ,width    : '66%'
      ,minValue : 0
      ,maxValue : (endTime - startTime) / 3600 / timeStepHours
      ,value    : (dNow.getTime() - new Date(startTime * 1000).getTime()) / (timeStepHours * 3600 * 1000)
      ,listeners : {
        change : function(e,slider,thumb,newVal,oldVal) {
          setMapTime(new Date(new Date(startTime * 1000).getTime() + newVal * timeStepHours * 3600 * 1000));
        }
        ,drag : function(e,slider,thumb) {
          sliderTooltip.showBy(thumb);
          var d = new Date(new Date(startTime * 1000).getTime() + slider.getValue()[0] * timeStepHours * 3600 * 1000);
          sliderTooltip.setHtml(dateToFriendlyString(d,utcOffset));
        }
        ,dragend : function() {
          sliderTooltip.hide();
        }
      }
    }
    ,{text : dateToAbbrevString(new Date(endTime * 1000)),handler : function() {
      var d = new Date(endTime * 1000);
      setMapTime(d);
      if (Ext.getCmp('timeSlider')) {
        Ext.getCmp('timeSlider').setValue((d.getTime() - new Date(startTime * 1000).getTime()) / (timeStepHours * 3600 * 1000));
      }
    }}
/*
    ,{xtype : 'spacer'}
    ,{iconCls : 'layers',width : '20%',maxWidth : 50,handler : function() {selectMapOptions()}}
*/
  ];
}

cacheImages([
   'img/close.gif'
  ,'OpenLayers/img/cloud-popup-relative.png'
  ,'img/spinner.gif'
]);

<?php
  if ($isMobile) {
?>
Ext.setup({
  viewport : {
    autoMaximize : true
  }
  ,onReady : function() {
    Ext.Viewport.add([
       mapComponent()
      ,{
         xtype      : 'toolbar'
        ,docked     : 'top'
        ,id         : 'toolbar'
        ,layout     : {
          align : 'center'
          ,pack : 'center'
        }
        ,defaults   : {
           iconMask : true
          ,ui       : 'plain'
        }
        ,items      : mapToolbarItems
      }
    ]);
    sliderTooltip = new Ext.Panel({
       floating : true
      ,width    : 250
      ,height   : 30
      ,style    : "background-color: #FFF;text-align:center"
    });
  }
});
<?php
  }
  else {
?>
function initExtJs() {
  var mapCmp = mapComponent();
  mapCmp.region = 'center';
  mapCmp.tbar = {items : [
    {
       text    : (function() {
         var d = new Date(new Date(startTime * 1000).getTime());
         var s = String.format('{0}',dateToFriendlyString(d,utcOffset)).split(' ');
         if (typeof utcOffset == 'string') {
           return '<span style="font-size:14px">' + s[0] + "<br>" + s[1] + ' ' + s[2] + '</span>';
         }
         else {
           return '<span style="font-size:14px">' + dateToAbbrevString(new Date(startTime * 1000)) + '</span>';
         }
      })()
      ,scale   : 'large'
      ,handler : function() {
        var cmp = Ext.getCmp('timeSlider');
        cmp.setValue(cmp.minValue);
        cmp.fireEvent('changecomplete',cmp,cmp.getValue());
      }
    }
/*
    ,{
       iconCls : 'rewind'
      ,scale   : 'large'
      ,handler : function() {
        var cmp = Ext.getCmp('timeSlider');
        cmp.setValue(0);
        cmp.fireEvent('changecomplete',cmp,cmp.getValue());
      }
    }
*/
    ,{
       iconCls : 'arrow_left'
      ,scale   : 'large'
      ,handler : function() {
        var cmp = Ext.getCmp('timeSlider');
        cmp.setValue(cmp.getValue() - 1);
        cmp.fireEvent('changecomplete',cmp,cmp.getValue());
      }
    }
    ,' '
    ,' '
    ,' '
    ,new Ext.Slider({
       id         : 'timeSlider'
      ,width      : 215
      ,minValue   : 0
      ,maxValue   : (endTime - startTime) / 3600 / timeStepHours
      ,value      : (dNow.getTime() - new Date(startTime * 1000).getTime()) / (timeStepHours * 3600 * 1000)
      ,plugins    : new Ext.slider.Tip({
        getText : function(thumb) {
          var d = new Date(new Date(startTime * 1000).getTime() + thumb.value * timeStepHours * 3600 * 1000);
          return String.format('<b>{0}</b>',dateToFriendlyString(d,utcOffset));
        }
        ,offsets : [0,47]
      })
      ,listeners    : {
        changecomplete : function(slider,val) {
          setMapTime(new Date(new Date(startTime * 1000).getTime() + val * timeStepHours * 3600 * 1000));
        }
      }
    })
    ,' '
    ,' '
    ,' '
    ,{
       iconCls : 'arrow_right'
      ,scale   : 'large'
      ,handler : function() {
        var cmp = Ext.getCmp('timeSlider');
        cmp.setValue(cmp.getValue() + 1);
        cmp.fireEvent('changecomplete',cmp,cmp.getValue());
      }
    }
/*
    ,{
       iconCls : 'fforward'
      ,scale   : 'large'
      ,handler : function() {
        var cmp = Ext.getCmp('timeSlider');
        cmp.setValue((endTime - startTime) / 3600 / timeStepHours);
        cmp.fireEvent('changecomplete',cmp,cmp.getValue());
      }
    }
*/
    ,{
       text    : (function(){
         var d = new Date(new Date(endTime * 1000).getTime());
         var s = String.format('{0}',dateToFriendlyString(d,utcOffset)).split(' ');
         if (typeof utcOffset == 'string') {
           return '<span style="font-size:14px">' + s[0] + "<br>" + s[1] + ' ' + s[2] + '</span>';
         }
         else {
           return '<span style="font-size:14px">' + dateToAbbrevString(new Date(endTime * 1000)) + '</span>';
         }
      })()
      ,scale   : 'large'
      ,handler : function() {
        var cmp = Ext.getCmp('timeSlider');
        cmp.setValue(cmp.maxValue);
        cmp.fireEvent('changecomplete',cmp,cmp.getValue());
      }
    }
    ,'->'
    ,{iconCls : 'layers',scale : 'large',handler : function() {selectMapOptions()}}
  ]};
  new Ext.Viewport({
     layout : 'border'
    ,items  : mapCmp
  });
}
<?php
  }
?>

function mapComponent() {
  return {
     xtype     : '<?php echo $isMobile ? 'container' : 'panel'?>'
<?php
  if ($isMobile) {
?>
    ,id        : 'map'
<?php
  }
  else {
?>
    ,html      : '<div style="width:100%;height:100%" id="map"></map>'
<?php
  }
?>
    ,listeners : {
      <?php echo $isMobile ? 'painted' : 'afterrender'?>  : function(c) {
<?php
  if ($isMobile) {
/*
?>
        if (typeof(scenarioId) != 'string') {
          Ext.Msg.show({
             message : 'Welcome!  Click anywhere on the map for a point forecast.'
            ,title   : 'MyMARACOOS Mobile'
            ,buttons : {text : 'Begin'}
          });
        }
<?php
*/
  }
  else {
?>
        Ext.Msg.buttonText.ok = 'Begin';
        if (typeof(scenarioId) != 'string') {
          Ext.Msg.alert(
             'MyMARACOOS Lite'
            ,'Welcome!  Click anywhere on the map for a point forecast.'
          );
        }
<?php
  }
?>

        var div = document.createElement('div');
        div.id = 'mapTimeDiv';
        div.innerHTML = '<table><tr><td style="padding-left:3px;text-align:left"><span class="mapHeader" id="mapTimeSpan">' + dateToFriendlyString(dNow,utcOffset) + '</span></td><td style="padding-right:3px;text-align:right"><span class="mapHeader" id="mapOverlays">&nbsp;</span></td></tr></table>';
        document.getElementById('map').appendChild(div);

        var div = document.createElement('div');
        div.id = 'activity';
        div.innerHTML = '<img src="img/spinner.gif">';
        document.getElementById('map').appendChild(div);

<?php
  if ($isMobile) {
?>
        var div = document.createElement('div');
        div.id = 'mapLayers';
        div.innerHTML = '<table><tr><td class="buttonLabel" align=center>Options</td></tr><tr><td align=center><a href="javascript:selectMapOptions()"><img src="img/mapLayers.png"></a></td></tr></table>';
        document.getElementById('map').appendChild(div);
<?php
  }
?>

        var div = document.createElement('div');
        div.id = 'legend';
        div.innerHTML = '<table><tr><td class="buttonLabel" align=center>Legend</td></tr><tr><td align=center>'
            + '<table cellspacing=0 cellpadding=0><tr>'
            + '<td id="legendTd"></td>'
            + '<td style="vertical-align : top"><table cellspacing=0 cellpadding=0><tr><td><a href="javascript:toggleLegend()"><img id="showHideLegendImage" class="legendControl" src="img/legend.png"></a></td></tr></table></td>'
            + '</tr></table>'
          + '</td></tr></table>';
        document.getElementById('map').appendChild(div);

        map = new OpenLayers.Map('map',{
           layers            : baselayers
          ,projection        : proj3857
          ,displayProjection : proj4326
          ,units             : 'm'
          ,maxExtent         : new OpenLayers.Bounds(-20037508,-20037508,20037508,20037508.34)
          ,controls          : [
             new OpenLayers.Control.Attribution()
            ,new OpenLayers.Control.Zoom()
<?php
  if ($isMobile) {
?>
            ,new OpenLayers.Control.TouchNavigation({dragPanOptions : {enableKinetic : true}})
<?php
  }
  else {
?>
            ,new OpenLayers.Control.ZoomBox()
            ,new OpenLayers.Control.Navigation()
<?php
  }
?>
            ,new OpenLayers.Control.Graticule({
               labelFormat     : 'dms'
              ,layerName       : 'grid'
              ,labelSymbolizer : {
                 fontColor   : "#666"
                ,fontSize    : "10px"
                ,fontFamily  : "tahoma,helvetica,sans-serif"
              }
              ,lineSymbolizer  : {
                 strokeWidth     : 0.40
                ,strokeOpacity   : 0.90
                ,strokeColor     : "#999999"
                ,strokeDashstyle : "dash"
              }
            })
          ]
        });

        map.events.register('moveend',this,function() {
          map.getLayersByName('OpenStreetMapOlay')[0].setVisibility(map.baseLayer.name == 'ESRI Ocean' && map.getZoom() >= 11);
        });

        map.events.register('changelayer',this,function(e) {
          if (e.property == 'visibility') {
            refreshLegend(document.getElementById('showHideLegendImage').src.indexOf('Delete') >= 0);
          }
          else if (e.property == 'params') {
            refreshLegendImages();
          }
        });

        if (!initCenter) {
<?php
  if ($isMobile) {
?>
          map.zoomToExtent(initExtent);
<?php
  }
  else {
?>
          // not sure why non-mobile needs this defered and scaled
          Ext.defer(function(){map.zoomToExtent(initExtent.scale(1.5))},100);
<?php
  }
?>
        }
        else {
          map.setCenter(new OpenLayers.LonLat(initCenter[0],initCenter[1]).transform(proj4326,proj3857),10);
        }

        // keep track if the next action will result in a real touchend event
        map.watchTouch = true;

        map.events.register('touchstart',this,function(e) {
          if (map.popup) {
            map.removePopup(map.popup);
            map.popup.destroy();
            map.popup = null;
          }
        });

<?php
  if ($isMobile) {
?>
        map.events.register(Ext.os.deviceType == 'Desktop' ? 'click' : 'touchend',this,function(e) {
<?php
  }
  else {
?>
        map.events.register('click',this,function(e) {
<?php
  }
?>
          if (!queryEnabled) {
            return;
          }
          if (map.watchTouch) {
            var ll = map.getLonLatFromPixel(e.xy);
            if (!ll) {
              return;
            }
            var lyr = map.getLayersByName('Query point')[0];
            lyr.removeFeatures(lyr.features);
            lyr.addFeatures(new OpenLayers.Feature.Vector(new OpenLayers.Geometry.Point(ll.lon,ll.lat)));
            var id = popup(ll,['<tr><td>Retrieving conditions...</td></tr><tr><td><img style="margin-top:10px" width=32 height=32 src="img/spinner.gif"></td></tr>'],(function(){map.getLayersByName('Query point')[0].removeFeatures(map.getLayersByName('Query point')[0].features)}));
            OpenLayers.Request.issue({
               url      : 'getPointForecast.php?nowOnly'
              ,method   : 'POST'
              ,headers  : {'Content-Type' : 'application/x-www-form-urlencoded'}
              ,data     : Ext.encode(buildGetFeatureInfoRequest(e.xy,true))
              ,callback : OpenLayers.Function.bind(showPointForecast,null,id,e.xy,ll)
            });
          }
          map.watchTouch = true;
        });
        map.events.register('movestart',this,function(e) {
          map.watchTouch = false;
        });

        var cDiv = document.createElement('div');
        cDiv.id = 'copyright';
        cDiv.innerHTML = 'Powered by <a href="http://asascience.com">ASA Coastmap</a>';
        document.getElementById('map').appendChild(cDiv);

        var startupLayers = [];

        for (var i = 0; i < overlays.length; i++) {
          overlays[i].category = 'forecast';
          overlays[i].mergeNewParams({TIME : makeTimeParam(dNow)});
          overlays[i].events.register('loadstart',this,function(e) {
            mapLoadstartMask(e.object.name);
          });
          overlays[i].events.register('loadend',this,function(e) {
            mapLoadendUnmask(e.object.name);
          });
          if (overlays[i].makeStyle) {
            overlays[i].mergeNewParams({STYLES : overlays[i].makeStyle(map.getZoom())});
          }
        }
        map.addLayers(overlays);

        for (var i = 0; i < overlays.length; i++) {
          if (overlays[i].initVisibility) {
            overlays[i].setVisibility(true);
          }
        }

        for (var i = 0; i < weather.length; i++) {
          weather[i].category = 'weather';
          if (weather[i].timeSensitive) {
            weather[i].mergeNewParams({TIME : makeTimeParam(dNow)});
          }
          weather[i].events.register('loadstart',this,function(e) {
            mapLoadstartMask(e.object.name);
          });
          weather[i].events.register('loadend',this,function(e) {
            mapLoadendUnmask(e.object.name);
          });
        }
        map.addLayers(weather);

        for (var i = 0; i < weather.length; i++) {
          if (weather[i].initVisibility) {
            weather[i].setVisibility(true);
          }
        }

        map.events.register('zoomend',this,function(e) {
          for (var i = 0; i < map.layers.length; i++) {
            if (map.layers[i].visibility && map.layers[i].makeStyle) {
              var s = map.layers[i].makeStyle(map.getZoom());
              if (s != map.layers[i].params.STYLES) {
                map.layers[i].mergeNewParams({STYLES : s});
              }
            }
          }
        });

        map.addLayer(new OpenLayers.Layer.Vector(
           'Query point'
          ,{styleMap : new OpenLayers.StyleMap({
            'default' : new OpenLayers.Style(OpenLayers.Util.applyDefaults({
               externalGraphic : 'img/Delete-icon.png'
              ,pointRadius     : 10
              ,graphicOpacity  : 1
              ,graphicWidth    : 16
              ,graphicHeight   : 16
            }))
          })}
        ));

        var glidersPointLyr = new OpenLayers.Layer.Vector(
           'Gliders'
          ,{visibility : false,styleMap : new OpenLayers.StyleMap({
            'default' : new OpenLayers.Style(
              {
                 pointRadius     : 12
                ,fillColor       : '${getFillColor}'
              }
              ,{
                context : {
                  getFillColor : function(f) {
                    return f.attributes.active ? '#ffff00' : '#a52a2a';
                  }
                }
              }
            )
            ,'select' : new OpenLayers.Style(
              {
                 pointRadius     : 12
                ,fillColor       : '${getFillColor}'
              }
              ,{
                context : {
                  getFillColor : function(f) {
                    return f.attributes.active ? '#ffff00' : '#a52a2a';
                  }
                }
              }
            )
          })}
        );
        glidersPointLyr.events.on({
          beforefeatureselected : function(e) {
            map.watchTouch = false;
          }
          ,featureselected : function(e) {
            var ll = new OpenLayers.LonLat(e.feature.geometry.x,e.feature.geometry.y);
            var ll4326 = ll.clone();
            ll4326.transform(proj3857,proj4326);
            var pLonLat = convertDMS(ll4326.lat.toFixed(5), "LAT") + ' ' + convertDMS(ll4326.lon.toFixed(5), "LON");
            var html = [
               '<tr><td colspan=2><b>' + dateToFriendlyString(new Date(e.feature.attributes.maxT * 1000),utcOffset) + '</b></tr></td>'
              ,'<tr><td colspan=2 style="font-color:gray;font-size:75%">' + pLonLat + '</td></tr>'
              ,'<tr><td>Provider</td><td>' + e.feature.attributes.provider + '</td></tr>'
              // ,'<tr><td>ID</td><td>' + e.feature.attributes.id + '</td></tr>'
              ,'<tr><td colspan=2><a target=_blank href="' + e.feature.attributes.url + '">glider provider page</a></td></tr>'
            ];
            popup(ll,html,(function(){glidersCtl.unselectAll()}));
          }
          ,featureunselected : function(e) {
            map.watchTouch = false;
          }
        });
        glidersPointLyr.legend = {
           title : 'Gliders'
          ,image : 'legends/Gliders.png'
        };

        var glidersTrackLyr = new OpenLayers.Layer.Vector(
           'Gliders Track'
          ,{visibility : false,styleMap : new OpenLayers.StyleMap({
            'default' : new OpenLayers.Style(
              {
                 strokeColor     : '${getStrokeColor}'
                ,strokeWidth     : '${getStrokeWidth}'
                ,strokeOpacity   : 0.5
              }
              ,{
                context : {
                  getStrokeColor : function(f) {
                    return f.attributes.active ? '#ffff00' : '#a52a2a';
                  }
                  ,getStrokeWidth : function(f) {
                    return f.attributes.active ? 8 : 4;
                  }
                }
              }
            )  
          })}
        );
        map.addLayer(glidersTrackLyr);
        map.addLayer(glidersPointLyr);

        glidersTrackLyr.setVisibility(<?php echo json_encode(!isset($_REQUEST['scenarioId']))?>);
        glidersPointLyr.setVisibility(<?php echo json_encode(!isset($_REQUEST['scenarioId']))?>);

        glidersPointLyr.events.register('visibilitychanged',this,function(e) {
          map.getLayersByName('Gliders Track')[0].setVisibility(e.object.visibility);
        });

        var json = <?php if (!isset($_REQUEST['scenarioId'])) {$activeOnly = true;include('getGliderPositions.php');} else {echo '[]';}?>;
        for (var i = 0; i < json.length; i++) {
          var geojson = new OpenLayers.Format.GeoJSON();
          var f       = geojson.read(json[i])[0];
          f.geometry.transform(proj4326,map.getProjectionObject());
          glidersPointLyr.addFeatures(f);
          var trkF = new OpenLayers.Format.WKT().read(f.attributes.track);
          trkF.attributes.active = f.attributes.active;
          trkF.geometry.transform(proj4326,map.getProjectionObject());
          glidersTrackLyr.addFeatures(trkF);
        }

        glidersCtl = new OpenLayers.Control.SelectFeature(glidersPointLyr,{
           clickout     : true
          ,toggle       : false
          ,multiple     : false
          ,hover        : false
          ,autoActivate : true
        });
        map.addControl(glidersCtl);

        if (typeof(scenarioId) == 'string') {
          Ext.defer(function() {
            var slider = Ext.getCmp('timeSlider');
            if (slider && typeof(slider.minValue) == 'number') {
              // non-mobile
              slider.setValue((slider.maxValue - slider.minValue) / 2);
              slider.fireEvent('changecomplete',slider,slider.getValue());
            }
            else if (slider) {
              // mobile
              slider.setValue((endTime - startTime) / 3600 / timeStepHours / 2);
              slider.fireEvent('change',null,null,null,slider.getValue()[0]);
            }
          },1000);
        }

<?php
  if (!$isMobile) {
?>
      map.updateSize();
<?php
  }
?>
      }
<?php
  if ($isMobile) {
?>
      ,resize : function() {
        if (map) {
          map.updateSize();
        }
      }
<?php
  }
  else {
?>
      ,bodyresize : function(p,w,h) {
        var el = document.getElementById('map');
        if (el) {
          el.style.width  = w;
          el.style.height = h;
          map.updateSize();
        }
      }
<?php
  }
?>
    }
  }
}

function selectMapOptions() {
  if (Ext.getCmp('playPauseButton')) {
    playPause(false);
  }
<?php
  if ($isMobile) {
?>
  Ext.getCmp('toolbar').setItems([
    {ui : 'back',text : 'Back',handler : function() {
      Ext.getCmp('overlaysForm').destroy();
      Ext.getCmp('toolbar').setItems(mapToolbarItems);
      Ext.getCmp('toolbar').setTitle('');
      if (Ext.getCmp('timeSlider')) {
        Ext.getCmp('timeSlider').setValue((dNow.getTime() - new Date(startTime * 1000).getTime()) / (timeStepHours * 3600 * 1000));
      }
    }}
    ,{xtype : 'spacer'}
  ]);
  Ext.getCmp('toolbar').setTitle('Map Options');
<?php
  }
?>

  var data = [];

  for (var i = 0; i < observationOrder.length; i++) {
    if (i == 0) {
      data.push({html : '<span class="sectionHeader">Observations</span>'});
    }
    data.push({
<?php
  if ($isMobile) {
?>
       label      : observationOrder[i]
      ,labelWidth : '80%'
      ,value      : observationOrder[i]
<?php
  }
  else {
?>
       boxLabel   : observationOrder[i]
      ,v          : observationOrder[i]
<?php
  }
?>
      ,checked    : map.getLayersByName(observationOrder[i])[0].visibility
      ,xtype      : '<?php echo $isMobile ? 'checkboxfield' : 'checkbox'?>'
      ,listeners  : {
<?php
  if ($isMobile) {
?>
        check    : function(cbox) {
          map.getLayersByName(cbox.getValue())[0].setVisibility(true);
        }
        ,uncheck : function(cbox) {
          map.getLayersByName(cbox.getValue())[0].setVisibility(false);
        }
<?php
  }
  else {
?>
        check    : function(cbox,cked) {
          map.getLayersByName(cbox.v)[0].setVisibility(cked);
        }
<?php
  }
?>
      }
    });
    if (i == observationOrder.length - 1) {
      data.push({html : '&nbsp;'});
    }
  }

  for (var i = 0; i < forecastOrder.length; i++) {
    if (i == 0) {
      data.push({html : '<span class="sectionHeader">Forecasts</span>'});
    }
    data.push({
<?php
  if ($isMobile) {
?>
       label      : forecastOrder[i]
      ,labelWidth : '80%'
      ,value      : forecastOrder[i]
<?php
  }
  else {
?>
       boxLabel   : forecastOrder[i]
      ,v          : forecastOrder[i]
<?php
  }
?>
      ,checked    : map.getLayersByName(forecastOrder[i])[0].visibility
      ,xtype      : '<?php echo $isMobile ? 'checkboxfield' : 'checkbox'?>'
      ,listeners  : {
<?php
  if ($isMobile) {
?>
        check    : function(cbox) {
          map.getLayersByName(cbox.getValue())[0].setVisibility(true);
        }
        ,uncheck : function(cbox) {
          map.getLayersByName(cbox.getValue())[0].setVisibility(false);
        }
<?php
  }
  else {
?>
        check    : function(cbox,cked) {
          map.getLayersByName(cbox.v)[0].setVisibility(cked);
        }
<?php
  }
?>
      }
    });
    if (i == forecastOrder.length - 1) {
      data.push({html : '&nbsp;'});
    }
  }

  data.push({html : '<span class="sectionHeader">' + (typeof(scenarioId) != 'string' ? 'Weather' : 'Oilspill model') + '</span>'});
  for (var i = 0; i < weatherOrder.length; i++) {
    var lyr = map.getLayersByName(weatherOrder[i])[0];
    // special case for scenario layer -- we only want change the params, not the layer
    if (lyr.customParams) {
      for (var j in lyr.customParams) {
        var checked = true;
        for (var k = 0; k < lyr.customParams[j].length; k++) {
          checked = checked && lyr.params[lyr.customParams[j][k]];
        }
        data.push({
<?php
  if ($isMobile) {
?>
           label      : j
          ,labelWidth : '80%'
          ,value      : {name : lyr.name,params : lyr.customParams[j]}
<?php
  }
  else {
?>
           boxLabel   : j
          ,v          : {name : lyr.name,params : lyr.customParams[j]}
<?php
  }
?>
          ,checked    : checked
          ,xtype      : '<?php echo $isMobile ? 'checkboxfield' : 'checkbox'?>'
          ,listeners  : {
<?php
  if ($isMobile) {
?>
            check    : function(cbox) {
              var p = {};
              var v = cbox.getValue();
              for (var l = 0; l < v.params.length; l++) {
                p[v.params[l]] = true;
              }
              map.getLayersByName(v.name)[0].mergeNewParams(p);
            }
            ,uncheck : function(cbox) {
              var p = {};
              var v = cbox.getValue();
              for (var l = 0; l < v.params.length; l++) {
                p[v.params[l]] = false;
              }
              map.getLayersByName(v.name)[0].mergeNewParams(p);
            }
<?php
  }
  else {
?>
            check    : function(cbox,cked) {
              var p = {};
              var v = cbox.v;
              for (var l = 0; l < v.params.length; l++) {
                p[v.params[l]] = cked;
              }
              map.getLayersByName(v.name)[0].mergeNewParams(p);
            }
<?php
  }
?>
          }
        });
      }
    }
    else {
      data.push({
<?php
  if ($isMobile) {
?>
         label      : weatherOrder[i]
        ,labelWidth : '80%'
        ,value      : weatherOrder[i]
<?php
  }
  else {
?>
         boxLabel   : weatherOrder[i]
        ,v          : weatherOrder[i]

<?php
  }
?>
        ,checked    : map.getLayersByName(weatherOrder[i])[0].visibility
        ,xtype      : '<?php echo $isMobile ? 'checkboxfield' : 'checkbox'?>'
        ,listeners  : {
<?php
  if ($isMobile) {
?>
          check    : function(cbox) {
            map.getLayersByName(cbox.getValue())[0].setVisibility(true);
          }
          ,uncheck : function(cbox) {
            map.getLayersByName(cbox.getValue())[0].setVisibility(false);
          }
<?php
  }
  else {
?>
          check    : function(cbox,cked) {
            map.getLayersByName(cbox.v)[0].setVisibility(cked);
          }
<?php
  }
?>
        }
      });
    }
  }

  data.push({html : '&nbsp;'});
  data.push({html : '<span class="sectionHeader">Backgrounds</span>'});
<?php
  if ($isMobile) {
?>
  for (var i = 0; i < baselayersOrder.length; i++) {
    data.push({
       label      : baselayersOrder[i]
      ,labelWidth : '80%'
      ,value      : baselayersOrder[i]
      ,name       : 'baseLayerRadio'
      ,checked    : map.baseLayer.name == baselayersOrder[i]
      ,xtype      : 'radiofield'
      ,listeners  : {
        check    : function(rbox) {
          map.setBaseLayer(map.getLayersByName(rbox.getLabel())[0]);
        }
      }
    });
  }
<?php
  }
  else {
?>
  var rgItems = [];
  for (var i = 0; i < baselayersOrder.length; i++) {
    rgItems.push({
       boxLabel   : baselayersOrder[i]
      ,value      : baselayersOrder[i]
      ,name       : 'baseLayerRadio'
      ,checked    : map.baseLayer.name == baselayersOrder[i]
    });
  }
  if (rgItems.length > 0) {
    data.push(new Ext.form.RadioGroup({
       id        : 'baselayerRadioGroup'
      ,columns   : 1
      ,items     : rgItems
      ,listeners : {change : function(rg,radio) {
        map.setBaseLayer(map.getLayersByName(radio.boxLabel)[0]);
      }}
    }));
  }
<?php
  }
?>

<?php
  if ($isMobile) {
?>
  var panel = new Ext.form.Panel({
     id    : 'overlaysForm'
    ,items : data
  });
  Ext.Viewport.add(panel);
  panel.show();
<?php
  }
  else {
?>
  var win = new Ext.Window({
     title  : 'Map Options'
    ,layout : 'fit'
    ,width  : 640 * 0.45
    ,height : 480 * 0.6
    ,modal  : true
    ,items  : new Ext.FormPanel({
       id         : 'overlaysForm'
      ,items      : data
      ,border     : false
      ,autoScroll : true
      ,bodyStyle  : 'padding:8px'
      ,defaults   : {border : false}
      ,labelWidth : 20
    })
    ,buttons  : [{text : 'Close',handler : function(){win.close()}}]
  });
  win.show();
<?php
  }
?>
}

function mapLoadstartMask(name) {
  loadingOverlays[name] = true;
  document.getElementById('activity').style.visibility = 'visible';
}

function mapLoadendUnmask(name) {
  delete loadingOverlays[name];
  var hits = 0;
  for (var i in loadingOverlays) {
    hits++;
  }
  if (hits == 0) {
    document.getElementById('activity').style.visibility = 'hidden';
  }
}

function makeTimeParam(d) {
  return d.getUTCFullYear() + '-' + leftPad(d.getUTCMonth() + 1,2) + '-' + leftPad(d.getUTCDate(),2) + 'T' + leftPad(d.getUTCHours(),2) + ':' + leftPad(d.getUTCMinutes(),2) + ':00';
}

function leftPad(value,padding) {
  var zeroes = "0";
  for (var i = 0; i < padding; i++) { zeroes += "0"; }
  return (zeroes + value).slice(padding * -1);
}

function dateToAbbrevString(e) {
  return e.format('h tt<br>ddd');
}

function dateToFriendlyString(e,utcOffset) {
  if (typeof utcOffset == 'string') {
    var d = new Date(e.getTime() + utcOffset * 3600 * 1000);
    var u = (utcOffset >= 0 ? '+' : '-') + leftPad(Math.abs(utcOffset),2);
    return d.format("UTC:yyyy-mm-dd HH:MM ") + u + 'Z';
  }
  var month = [
     'Jan'
    ,'Feb'
    ,'Mar'
    ,'Apr'
    ,'May'
    ,'Jun'
    ,'Jul'
    ,'Aug'
    ,'Sep'
    ,'Oct'
    ,'Nov'
    ,'Dec'
  ];

  var c = "";
  var a = new Date();
  if (a.getDate() == e.getDate()) {
    strDay = "today"
  } else {
    var b = new Date(a.getTime() + 86400000);
    var d = new Date(a.getTime() - 86400000);
    if (b.getDate() == e.getDate()) {
      strDay = "tomorrow"
    } else {
      if (d.getDate() == e.getDate()) {
        strDay = "yesterday"
      } else {
        aryDays = new Array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
        strDay = aryDays[e.getDay()]
      }
    }
  }
  c += (e.getHours() > 12 ? e.getHours() - 12 : (e.getHours() == 0 ? 12 : e.getHours()));
  c += ":" + (e.getMinutes() < 10 ? "0" : "") + e.getMinutes() + (e.getHours() > 11 ? " pm" : " am");
  c += " " + strDay;
  c += ' (' + e.format('Z') + ')';

  // show a real date if over a week old
  if (e < new Date(a.getTime() - 1000 * 3600 * 24 * 7)) {
    return month[e.getMonth()] + ' ' + e.getDate() + ', ' + e.getFullYear();
  }
  else {
    return c;
  }
}

function showPointForecast(id,xy,ll,r) {
  var json = new OpenLayers.Format.JSON().read(r.responseText);
  var data = {};
  var hits = 0;
  if (json) {
    for (var i in json) {
      if (json[i].lyr) {
        var lyr = map.getLayersByName(json[i].lyr)[0];
        if (lyr.getFeatureInfo && lyr.getFeatureInfo.columns && lyr.getFeatureInfo.columns[i]) {
          data[lyr.name] = '<tr>'
            + '<td>' + lyr.getFeatureInfo.columns[i].name + '</td>'
            + '<td>' + lyr.getFeatureInfo.columns[i].format(json) + '</td>'
            + '</tr>';
          hits++;
        }
      }
    }
  }
  var ll4326 = ll.clone(); // new OpenLayers.LonLat(ll.lon,ll.lat).transform(proj3857,proj4326);
  ll4326.transform(proj3857,proj4326);
  var pLonLat = convertDMS(ll4326.lat.toFixed(5), "LAT") + ' ' + convertDMS(ll4326.lon.toFixed(5), "LON");
  var html = hits > 0 ? ['<tr><td colspan=2><b>' + dateToFriendlyString(new Date(json.time * 1000),utcOffset) + '</b></tr></td>','<tr><td colspan=2 style="font-color:gray;font-size:75%">' + pLonLat + '</td></tr>'] : ['Conditions<br>unavailable'];
  for (var i = 0; i < forecastOrder.length; i++) {
    if (data[forecastOrder[i]]) {
      html.push(data[forecastOrder[i]]); 
    }
  }
  if (hits > 0 && <?php echo json_encode($isMobile)?>) {
    html.push('<tr><td colspan=2><a id="getTimeSeries" href="javascript:getTimeSeries(' + xy.x + ',' + xy.y + ')">view graph</a></td></tr>');
  }
  if (map.popup && map.popup.id == id) {
    var ll4326 = ll.clone(); // new OpenLayers.LonLat(ll.lon,ll.lat).transform(proj3857,proj4326)
    ll4326.transform(proj3857,proj4326);
    popup(ll,html,(function(){map.getLayersByName('Query point')[0].removeFeatures(map.getLayersByName('Query point')[0].features)}));
  }
}

function buildGetFeatureInfoRequest(xy,nowOnly) {
  var gfi = {};
  for (var i = 0; i < map.layers.length; i++) {
    if (map.layers[i].getFeatureInfo && map.layers[i].visibility) {
      var lyr = map.layers[i];
      var origParams = OpenLayers.Util.getParameters(lyr.getFullRequestString({}));
      var gfiParams  = {
         SERVICE      : 'WMS'
        ,VERSION      : '1.1.1'
        ,REQUEST      : 'GetFeatureInfo'
        ,SRS          : origParams['SRS']
        ,EXCEPTIONS   : 'application/vnd.ogc.se_xml'
        ,INFO_FORMAT  : (/Bottom water temp|NECOFS/.test(lyr.name) ? 'text/csv' : 'text/xml')
        ,BBOX         : map.getExtent().toBBOX()
        ,X            : Math.round(xy.x)
        ,Y            : Math.round(xy.y)
        ,WIDTH        : map.size.w
        ,HEIGHT       : map.size.h
        ,TIME         : (nowOnly ? origParams['TIME'] : (makeTimeParam(new Date(dNow.getTime() - 12 * 3600 * 1000)) + '/' + makeTimeParam(new Date(dNow.getTime() + 36 * 3600 * 1000))))
        ,QUERY_LAYERS : origParams['LAYERS']
      };
      if (lyr.getFeatureInfo && lyr.getFeatureInfo.extraParams) {
        for (var j = 0; j < lyr.getFeatureInfo.extraParams.length; j++) {
          gfiParams[lyr.getFeatureInfo.extraParams[j]] = origParams[lyr.getFeatureInfo.extraParams[j]];
        }
      }
      var p = [];
      for (var j in gfiParams) {
        p.push(j + '=' + gfiParams[j]);
      }
      gfi[lyr.name] = lyr.url + '?' + p.join('&');
    }
  }
  return gfi;
}

function popup(ll,html,action) {
  var id = new Date().getTime() + Math.random();
  map.popup = new OpenLayers.Popup.FramedCloud(
     'popup'
    ,ll
    ,null
    ,'<table style="width:200px">' + html.join('') + '</table>'
    ,null
    ,true
    ,function() {
      action();
      map.watchTouch = false;
      map.removePopup(map.popup);
      map.popup.destroy();
      map.popup = false;
      Ext.defer(function(){map.watchTouch = true},100);
    }
  );
  map.popup.id = id;
  map.addPopup(map.popup,true);
  OpenLayers.Event.observe(map.popup.contentDiv,'touchend',OpenLayers.Function.bindAsEventListener(function(e) {
    OpenLayers.Event.stop(e);
  }));
  var el = document.getElementById('getTimeSeries');
  if (el) {
    OpenLayers.Event.observe(el,'touchend',OpenLayers.Function.bindAsEventListener(function(e) {
      OpenLayers.Event.stop(e);
    }));
  }
  for (var i = 0; i < map.popup.blocks.length; i++) {
    // blocks[1] is the NE curve of the cloud, and we want it to also close the popup
    if (i == 1) {
      OpenLayers.Event.observe(map.popup.blocks[i].div,'touchend',OpenLayers.Function.bindAsEventListener(function(e) {
        OpenLayers.Event.stop(e);
        action();
        map.watchTouch = false;
        map.removePopup(map.popup);
        map.popup.destroy();
        map.popup = false;
        Ext.defer(function(){map.watchTouch = true},100);
      }));
    }
    else {
      OpenLayers.Event.observe(map.popup.blocks[i].div,'touchend',OpenLayers.Function.bindAsEventListener(function(e) {
        OpenLayers.Event.stop(e);
      }));
    }
  }
  return id;
}

function getTimeSeries(x,y) {
  Ext.getCmp('toolbar').setItems([
    {ui : 'back',text : 'Back',handler : function() {
      Ext.getCmp('fullPointForecast').destroy();
      Ext.getCmp('toolbar').setItems(mapToolbarItems);
      Ext.getCmp('toolbar').setTitle('');
    }}
    ,{xtype : 'spacer'}
  ]);
  Ext.getCmp('toolbar').setTitle('Point forecast');
  var panel = new Ext.Panel({
     id         : 'fullPointForecast'
    ,fullscreen : true
    ,scrollable : true
    ,items      : {html : '<img src="img/spinner.gif">'}
    ,listeners  : {resize : function() {
      Ext.getCmp('fullPointForecast').getItems().each(function(p) {
        var id = p.id.split('_')[1];
        var el = document.getElementById('chart_' + id.replace(/ |\(|\)/g,''));
        el.style.width = (Ext.Viewport.getWindowWidth() - 20) + 'px';
        drawChart(id);
      });
    }}
  });
  Ext.Viewport.add(panel);
  panel.show();

  OpenLayers.Request.issue({
     url      : 'getPointForecast.php'
    ,method   : 'POST'
    ,headers  : {'Content-Type' : 'application/x-www-form-urlencoded'}
    ,data     : Ext.encode(buildGetFeatureInfoRequest({x : x,y : y},false))
    ,callback : OpenLayers.Function.bind(showFullPointForecast,null)
  });
}

function showFullPointForecast(r) {
  var json = new OpenLayers.Format.JSON().read(r.responseText);
  charts   = {};
  var itemsH  = {};
  var data    = {};
  var titles  = {};
  var minDate;
  var maxDate;

  if (json) {
    for (var i in json) {
      for (var j = 0; j < json[i].t.length; j++) {
        if (!minDate || new Date(json[i].t[j] * 1000) < minDate) {
          minDate = new Date(json[i].t[j] * 1000);
        }
        if (!maxDate || new Date(json[i].t[j] * 1000) > maxDate) {
          maxDate = new Date(json[i].t[j] * 1000);
        }
      }
    }
    for (var i in json) {
      if (json[i].lyr) {
        var lyr = map.getLayersByName(json[i].lyr)[0];
        if (lyr.charts && lyr.charts[i]) {
          itemsH[lyr.name] = {
             html      : '<table><tr><td><img style="width:10px" src="img/blank.png"></td><td>' + lyr.charts[i].name + '<br>' + '<div style="height:130px;width:' + (Ext.Viewport.getWindowWidth() - 20) + 'px" id="chart_' + lyr.name.replace(/ |\(|\)/g,'') + '"></div></td><td><img style="width:10px" src="img/blank.png"></td></tr>'
            ,height    : 160
            ,id        : 'chartItem_' + lyr.name.replace(/ |\(|\)/g,'')
            ,listeners : {painted : function(p) {
              makeChart(p.id.split('_')[1].replace(/ |\(|\)/g,''));
            }}
          };
          titles[lyr.name.replace(/ |\(|\)/g,'')] = lyr.charts[i].name;
          var d     = [];
          var dNowA = [];
          var dMapA = [];
          var dVecA = [];
          for (var j = 0; j < json[i].t.length; j++) {
            var cData = lyr.charts[i].format(json,j);
            d.push([cData[0],cData[1]]);
            if (cData[2]) {
              dVecA.push([cData[0],cData[1],cData[2]]);
            }
            // interpolate now
            if (j < json[i].t.length - 1 && new Date(json[i].t[j] * 1000) <= new Date() && new Date() <= new Date(json[i].t[j + 1] * 1000)) {
              var t  = new Date().getTime();
              var t0 = new Date(json[i].t[j] * 1000).getTime();
              var t1 = new Date(json[i].t[j + 1] * 1000).getTime();
              var v0 = lyr.charts[i].nowVal(json,j);
              var v1 = lyr.charts[i].nowVal(json,j + 1);
              if (t1 - t0 != 0) {
                var theta = Math.atan((v1 - v0) / (t1 - t0));
                dNowA = [new Date(),Math.sin(theta) * (t - t0) + v0];
              }
            }
            // interpolate map time (dNow)
            if (j < json[i].t.length - 1 && new Date(json[i].t[j] * 1000) <= dNow && dNow <= new Date(json[i].t[j + 1] * 1000)) {
              var t  = dNow;
              var t0 = new Date(json[i].t[j] * 1000).getTime();
              var t1 = new Date(json[i].t[j + 1] * 1000).getTime();
              var v0 = lyr.charts[i].nowVal(json,j);
              var v1 = lyr.charts[i].nowVal(json,j + 1);
              if (t1 - t0 != 0) {
                var theta = Math.atan((v1 - v0) / (t1 - t0));
                dMapA = [dNow,Math.sin(theta) * (t - t0) + v0];
              }
            }
          }
          data[lyr.name.replace(/ |\(|\)/g,'')] = {
             data : d
            ,min  : minDate
            ,max  : maxDate
            ,now  : dNowA
            ,map  : dMapA
            ,vec  : dVecA
          };
        }
      }
    }
  }

  var items = [];
  for (var i = 0; i < forecastOrder.length; i++) {
    if (itemsH[forecastOrder[i]]) {
      items.push(itemsH[forecastOrder[i]]);
    }
  }
  items.push({
     html      : '<table style="width:100%"><tr><td class="forecastLegend"><span style="border-bottom: 2px solid #32CD32">Green line</span> : current time<br><span style="border-bottom: 2px solid #0000ff">Blue line</span> : map time</td></tr></table>'
    ,height    : 50
  });
  Ext.getCmp('fullPointForecast').setItems(items);

  function makeChart(id) {
    var markings = [];
    if (data[id]['map'][0]) {
      markings.push({color : '#0000ff',lineWidth : 2,xaxis : {from : data[id]['map'][0].getTime(),to : data[id]['map'][0].getTime()}});
    }
    if (data[id]['now'][0]) {
      markings.push({color : '#32CD32',lineWidth : 2,xaxis : {from : data[id]['now'][0].getTime(),to : data[id]['now'][0].getTime()}});
    }
    charts[id] = {
       id     : $('#' + 'chart_' + id)
      ,data   : [{label : titles[id],data : data[id].data,color : '#8DA0CB',curvedLines : {show : true}}]
      ,config : {
         xaxis  : {min : data[id]['min'].getTime(),max : data[id]['max'].getTime(),mode : 'time',timezone : 'browser',twelveHourClock : true,timeformat : "%a<br>%l %p",labelHeight : 35}
        ,yaxis  : {labelWidth : 35}
        ,grid   : {backgroundColor : {colors : ['#fff','#eee']},borderWidth : 1,borderColor : '#99BBE8',hoverable : true,markings : markings}
        ,legend : {show : false}
        ,series : {curvedLines : {active : true}}
      }
      ,nowData : data[id]['now']
      ,mapData : data[id]['map']
      ,vecData : data[id]['vec']
    };
    var p = $.plot(charts[id]['id'],charts[id]['data'],charts[id]['config']);
    drawChart(id);
  }
}

function cacheImages(a) {
  for (var i = 0; i < a.length; i++) {
    var img = new Image();
    img.src = a[i];
  }
}

function degreesToCompass(d) {
  var compass = [
     'N'
    ,'NNE'
    ,'NE'
    ,'ENE'
    ,'E'
    ,'ESE'
    ,'SE'
    ,'SSE'
    ,'S'
    ,'SSW'
    ,'SW'
    ,'WSW'
    ,'W'
    ,'WNW'
    ,'NW'
    ,'NNW'
  ];
  return compass[Math.abs(Math.round((Number(d) + 22.5) / 22.5 - 0.5) % 16)];
}

function isNumber(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}

function toggleLegend() {
  var img = document.getElementById('showHideLegendImage');
  if (img.src.indexOf('Delete') >= 0) {
    img.src = img.src.replace('Delete','');
    refreshLegend(false);
  }
  else {
    img.src = img.src.split('.png')[0] + 'Delete.png';
    refreshLegend(true);
  }
}

function refreshLegendImages() {
  for (var i = 0; i < map.layers.length; i++) {
    var img = document.getElementById('legend.' + map.layers[i].name);
    if (img && map.layers[i].legend.image == 'getLegendGraphic') {
      img.src = 'getLegend.php?u=' + encodeURIComponent(map.layers[i].getFullRequestString({REQUEST : 'GetLegendGraphic',BBOX : map.getExtent()}));
    }
  }
}

function refreshLegend(on) {
  var legTd = document.getElementById('legendTd');
  while (legTd.hasChildNodes()) {
    legTd.removeChild(legTd.lastChild);
  }

  // pull out the titles for the title bar
  var a = [];
  var hits = 0;
  for (var i = 0; i < observationOrder.length; i++) {
    var lyr = map.getLayersByName(observationOrder[i])[0];
    if (lyr && lyr.visibility) {
      a.push(lyr.name);
      if (lyr.legend) {
        hits++;
      }
    }
  }
  for (var i = 0; i < forecastOrder.length; i++) {
    var lyr = map.getLayersByName(forecastOrder[i])[0];
    if (lyr && lyr.visibility) {
      a.push(lyr.name);
      if (lyr.legend) {
        hits++;
      }
    }
  }
  for (var i = 0; i < weatherOrder.length; i++) {
    var lyr = map.getLayersByName(weatherOrder[i])[0];
    if (lyr && lyr.visibility) {
      a.push(lyr.name);
      if (lyr.legend) {
        hits++;
      }
    }
  }
  document.getElementById('mapOverlays').innerHTML = a.join(', ').substr(0,20) + (a.join(', ').length > 20 ? '...' : '');

  document.getElementById('legend').style.visibility = (hits > 0 ? 'visible' : 'hidden');
  if (!on) {
    return;
  }

  // populate the legend table
  var table = document.createElement('table');
  for (var i = 0; i < observationOrder.length; i++) {
    var lyr = map.getLayersByName(observationOrder[i])[0];
    if (lyr.visibility && lyr.legend) {
      var tr = document.createElement('tr');
      var td = document.createElement('td');
      var imgSrc = lyr.legend.image;
      if (lyr.legend.title) {
        td.innerHTML = '<span class="mapHeader">' + lyr.legend.title + '</span><br>';
      }
      td.innerHTML += '<img id="legend.' + lyr.name + '" src="' + imgSrc + '">';
      tr.appendChild(td);
      table.appendChild(tr);
    }
  }
  for (var i = 0; i < forecastOrder.length; i++) {
    var lyr = map.getLayersByName(forecastOrder[i])[0];
    if (lyr.visibility && lyr.legend) {
      var tr = document.createElement('tr');
      var td = document.createElement('td');
      var imgSrc = lyr.legend.image;
      if (imgSrc == 'getLegendGraphic') {
        imgSrc = lyr.getFullRequestString({REQUEST : 'GetLegendGraphic',BBOX : map.getExtent()});
      }
      if (lyr.legend.title) {
        td.innerHTML = '<span class="mapHeader">' + lyr.legend.title + '</span><br>';
      }
      td.innerHTML += '<img id="legend.' + lyr.name + '" src="' + imgSrc + '">';
      tr.appendChild(td);
      table.appendChild(tr);
    }
  }
  for (var i = 0; i < weatherOrder.length; i++) {
    var lyr = map.getLayersByName(weatherOrder[i])[0];
    if (lyr.visibility && lyr.legend) {
      var tr = document.createElement('tr');
      var td = document.createElement('td');
      var imgSrc = lyr.legend.image;
      if (imgSrc == 'getLegendGraphic') {
        imgSrc = 'getLegend.php?u=' + encodeURIComponent(lyr.getFullRequestString({REQUEST : 'GetLegendGraphic',BBOX : map.getExtent()}));
      }
      if (lyr.legend.title) {
        td.innerHTML = '<span class="mapHeader">' + lyr.legend.title + '</span><br>';
      }
      td.innerHTML += '<img id="legend.' + lyr.name + '" src="' + imgSrc + '">';
      tr.appendChild(td);
      table.appendChild(tr);
    }
  }
  legTd.appendChild(table);
}

function drawChart(id) {
  var p = $.plot(charts[id]['id'],charts[id]['data'],charts[id]['config']);
  for (var i = 0; i < charts[id]['vecData'].length; i++) {
    var o = p.pointOffset({x : charts[id]['vecData'][i][0],y : charts[id]['vecData'][i][1]});
    charts[id]['id'].prepend('<div class="dir" style="position:absolute;left:' + (o.left-80/2) + 'px;top:' + (o.top-(80/2)) + 'px;background-image:url(\'./img/arrows/' + 80 + 'x' + 80 + '.dir' + Math.round(charts[id]['vecData'][i][2]) + '.' + '7570B3' + '.png\');width:' + 80 + 'px;height:' + 80 + 'px;"></div>');
  }
}

function convertDMS (coordinate, type, spaceOnly) {
  var coords = new Array();

  abscoordinate = Math.abs(coordinate)
  coordinatedegrees = Math.floor(abscoordinate);

  coordinateminutes = (abscoordinate - coordinatedegrees)/(1/60);
  tempcoordinateminutes = coordinateminutes;
  coordinateminutes = Math.floor(coordinateminutes);
  coordinateseconds = (tempcoordinateminutes - coordinateminutes)/(1/60);
  coordinateseconds =  Math.round(coordinateseconds*10000);
  coordinateseconds /= 10000;

  if( coordinatedegrees < 10 )
    coordinatedegrees = "0" + coordinatedegrees;

  if( coordinateminutes < 10 )
    coordinateminutes = "0" + coordinateminutes;

  if( coordinateseconds < 10 ) {
    coordinateseconds = "0" + coordinateseconds.toFixed(3);
  }
  else {
    coordinateseconds = coordinateseconds.toFixed(3);
  }

  if (spaceOnly) {
    var factor = 1;
    if (coordinate < 0) {
      factor = -1;
    }
    return factor * coordinatedegrees + ' ' + coordinateminutes + ' ' + coordinateseconds + ' ';
  }
  else {
    return coordinatedegrees + '&deg; ' + coordinateminutes + "' " + coordinateseconds + '" ' + this.getHemi(coordinate, type);
  }
}

/**
 * Return the hemisphere abbreviation for this coordinate.
 */
function getHemi(coordinate, type) {
  var coordinatehemi = "";
  if (type == 'LAT') {
    if (coordinate >= 0) {
      coordinatehemi = "N";
    }
    else {
      coordinatehemi = "S";
    }
  }
  else if (type == 'LON') {
    if (coordinate >= 0) {
      coordinatehemi = "E";
    } else {
      coordinatehemi = "W";
    }
  }

  return coordinatehemi;
}

function setMapTime(d) {
  for (var i = 0; i < map.layers.length; i++) {
    var lyr = map.layers[i];
    if (lyr.timeSensitive) {
      lyr.mergeNewParams({TIME : makeTimeParam(d)});
    }
  }
  dNow = d;
  document.getElementById('mapTimeSpan').innerHTML = dateToFriendlyString(d,utcOffset);
}

function resetTime() {
  if (startTime != 'now') {
    return new Date(startTime * 1000);
  }
  else {
    var d = new Date();
    d.setUTCMinutes(0);
    d.setUTCSeconds(0);
    d.setUTCMilliseconds(0);
    var dt = d.getUTCHours() % timeStepHours;
    // Snap it to the nearest timeStepHours.  This currently works for timeStepHours = 3.
    return new Date(d.getTime() + (dt == 1 ? -1 : dt == 2 ? 1 : 0) * 3600 * 1000);
  }
}

function goPrevTimeStep() {
  var d = new Date(dNow.getTime() - timeStepHours * 3600 * 1000);
  if (startTime == 'now' || d.getTime() >= new Date(startTime * 1000)) {
    setMapTime(d);
    var ts = Ext.getCmp('timeSlider');
    if (ts) {
      ts.setValue(ts.getValue() - 1);
    }
  }
  playPause(false);
}

function goNextTimeStep(manual) {
  var d = new Date(dNow.getTime() + timeStepHours * 3600 * 1000);
  if (endTime == 'none' || d.getTime() <= new Date(endTime * 1000)) {
    setMapTime(d);
    var ts = Ext.getCmp('timeSlider');
    if (ts) {
      ts.setValue(Number(ts.getValue()) + 1);
    }
  }
  else {
    playPause(false);
  }
  if (manual && animating) {
    playPause(false);
  }
}

function playPause(play) {
  Ext.getCmp('playPauseButton').<?php echo $isMobile ? 'setIconCls' : 'setIconClass'?>(play ? 'pause' : 'play');
  if (play && !animating) {
    Ext.defer(function() {
      animate();
    },animateTimeout);
  }
  else {
    animating = false;
    clearTimeout(animateTimer);
  }
}

function animate() {
  animating = true;
  animateTimer = setTimeout('animate()',animateTimeout);
  goNextTimeStep(false);
}

function rewind() {
  playPause(false);
  setMapTime(new Date(startTime * 1000));
}

function fforward() {
  playPause(false);
  setMapTime(new Date(endTime * 1000));
}

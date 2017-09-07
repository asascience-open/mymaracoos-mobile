var weather = [
  new OpenLayers.Layer.WMS(
     'Radar'
    ,'https://nowcoast.noaa.gov/arcgis/services/nowcoast/radar_meteo_imagery_nexrad_time/MapServer/WMSServer'
    ,{
       layers      : '1'
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
      ,initVisibility   : false
      ,opacity          : 0.7
      ,noMagic          : true
      ,transitionEffect : 'resize'
      ,legend           : {
         title : 'Radar reflectivity (dB)'
        ,image : 'legends/RADAR.png'
      }
      ,getFeatureInfo   : false
    }
  )
/*
  new OpenLayers.Layer.TMS(
     'WWA'
    ,[
       'http://radarcache0.srh.noaa.gov/tc/tc.py/'
      ,'http://radarcache1.srh.noaa.gov/tc/tc.py/'
      ,'http://radarcache2.srh.noaa.gov/tc/tc.py/'
      ,'http://radarcache3.srh.noaa.gov/tc/tc.py/'
      ,'http://radarcache4.srh.noaa.gov/tc/tc.py/'
    ]
   ,{
       layername   : 'threat'
      ,isBaseLayer : false
      ,projection  : proj3857
      ,opacity     : 0.3
      ,type        : 'png'
      ,getURL      : function (bounds) {
        bounds = this.adjustBounds(bounds);
        var res = this.map.getResolution();
        var x = Math.round((bounds.left - this.tileOrigin.lon) / (res * this.tileSize.w));
        // var y = Math.round((bounds.bottom - this.tileOrigin.lat) / (res * this.tileSize.h));
        var y = Math.round((this.maxExtent.top - bounds.top) / (res * this.tileSize.h));
        var z = this.serverResolutions != null ?
            OpenLayers.Util.indexOf(this.serverResolutions, res) :
            this.map.getZoom() + this.zoomOffset;
        z += map.baseLayer.minZoomLevel ? map.baseLayer.minZoomLevel : 0;
        var path = this.serviceVersion + "/" + this.layername + "/" + z + "/" + x + "/" + y + "." + this.type;
        var url = this.url;
        if (OpenLayers.Util.isArray(url)) {
            url = this.selectUrl(path, url);
        }
        return url + path;
      }
      ,transitionEffect : 'resize'
      ,getFeatureInfo   : false
    }
  )
  ,new OpenLayers.Layer.WMS(
     'Marine zones'
    ,'http://db1.charthorizon.com/races-cgi-bin/mapserv?map=/home/map/mapper/prod/htdocs/nws/zones.map&'
    ,{
       layers      : 'hz,mz'
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
      ,initVisibility   : true
      ,opacity          : 1
      ,noMagic          : true
      ,transitionEffect : 'resize'
      ,getFeatureInfo   : false
    }
  )
*/
/*
  ,new OpenLayers.Layer.WMS(
     'NHC storm tracks'
    ,'https://nowcoast.noaa.gov/arcgis/services/nowcoast/wwa_meteocean_tropicalcyclones_trackintensityfcsts_time/MapServer/WMSServer'
    ,{
       layers      : '0,1,2,3,4,5,6,7,8,9'
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
      ,initVisibility   : true
      ,opacity          : 0.5
      ,noMagic          : true
      ,transitionEffect : 'resize'
      ,getFeatureInfo   : false
    }
  )
*/
  ,new OpenLayers.Layer.ArcGIS93Rest(
     'NHC storm tracks'
    ,'http://utility.arcgis.com/usrsvcs/servers/4422573bd4324cfa86b2a3774063f6cc/rest/services/LiveFeeds/Hurricane_Active/MapServer/export'
    ,{
       layers      : 'show:0,1,2,3,4'
      ,transparent : true
    }
    ,{
       isBaseLayer      : false
      ,projection       : proj3857
      ,singleTile       : true
      ,wrapDateLine     : true
      ,visibility       : false
      ,initVisibility   : true
      ,opacity          : 0.5
      ,noMagic          : true
      ,transitionEffect : 'resize'
      ,getFeatureInfo   : false
    }
  )
  ,new OpenLayers.Layer.Image(
     'Blank weather'
    ,'img/blank.png'
    ,new OpenLayers.Bounds(0,0,0,0)
    ,new OpenLayers.Size(0,0)
    ,{
       isBaseLayer      : false
      ,projection       : proj3857
      ,singleTile       : true
      ,wrapDateLine     : true
      ,visibility       : false
      ,initVisibility   : false
      ,opacity          : 1
      ,noMagic          : true
      ,transitionEffect : 'resize'
      ,getFeatureInfo   : false
    }
  )
];

var weatherOrder = [
   'NHC storm tracks'
  ,'Radar'
];

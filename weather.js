var weather = [
  new OpenLayers.Layer.WMS(
     'Radar'
    ,'http://coastmap.com/ecop/wms.aspx'
    ,{
       layers      : 'NEXRAD_RADAR'
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
  ,new OpenLayers.Layer.WMS(
     'NHC storm tracks'
    ,'http://nowcoast.noaa.gov/wms/com.esri.wms.Esrimap/wwa?BGCOLOR=0xCCCCFE&'
    ,{
       layers      : 'NHC_TRACK_POLY,NHC_TRACK_LIN,NHC_TRACK_PT,NHC_TRACK_PT_72DATE,NHC_TRACK_PT_120DATE,NHC_TRACK_PT_0NAMEDATE,NHC_TRACK_PT_MSLPLABELS,NHC_TRACK_PT_72WLBL,NHC_TRACK_PT_120WLBL,NHC_TRACK_PT_72CAT,NHC_TRACK_PT_120CAT'
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

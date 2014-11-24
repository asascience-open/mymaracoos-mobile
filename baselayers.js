var baselayers = [
  new OpenLayers.Layer.XYZ(
     'ESRI Ocean'
    ,'http://services.arcgisonline.com/ArcGIS/rest/services/Ocean_Basemap/MapServer/tile/${z}/${y}/${x}.jpg'
    ,{
       sphericalMercator : true
      ,isBaseLayer       : true
      ,wrapDateLine      : true
    }
  )
  ,new OpenLayers.Layer.XYZ(
     'ESRI Street Map'
    ,'http://services.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/${z}/${y}/${x}.jpg'
    ,{
       sphericalMercator : true
      ,isBaseLayer       : true
      ,wrapDateLine      : true
    }
  )
  ,new OpenLayers.Layer.OSM(
     'OpenStreetMapOlay'
    ,'http://tile.openstreetmap.org/${z}/${x}/${y}.png'
    ,{
       isBaseLayer : false
    }
  )
  ,new OpenLayers.Layer.Bing({
     type             : 'Aerial'
    ,name             : 'Bing Aerial'
    ,key              : 'AhAUXqyQl8MCSgPDlvRf8Dk6fj11yE3qZYcehpG5f7gpea6JVRD9lHCDE8DMawH2'
    ,transitionEffect : 'resize'
  })
  ,new OpenLayers.Layer.ArcGIS93Rest(
     'Nautical Charts'
    ,'http://egisws02.nos.noaa.gov/ArcGIS/rest/services/RNC/NOAA_RNC/MapServer/export'
    ,{
      layers : 'show:3'
    }
    ,{
      isBaseLayer : true
    }
  )
];

var baselayersOrder = [
   'Bing Aerial'
  ,'ESRI Ocean'
  ,'ESRI Street Map'
  ,'Nautical Charts'
];

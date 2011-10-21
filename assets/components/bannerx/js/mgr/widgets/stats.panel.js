Bannerx.panel.Stats = function(config) {
    config = config || {};
    Ext.apply(config,{
        border: false
        ,baseCls: 'modx-panel'
        ,items: [{
            html: '<h2>'+_('bannerx.stats.today')+'</h2>'
            ,border: false
            ,cls: 'modx-page-header'
        },{
            xtype:'columnchart'
            ,url: Bannerx.config.baseUrl + 'manager/assets/ext3/resources/charts.swf'
            ,xField: 'name'
            ,yField: 'clicks'
            ,height: 200
            ,store: new Ext.data.JsonStore({
                url: Bannerx.config.connectorUrl
                ,baseParams: {
                    action: 'mgr/ads/getclicks'
                    ,period: '%Y-%m-%d'
                }
                ,fields: ['name', 'clicks']
                ,autoLoad: true
                ,root: 'results'
            })
        },{
            html: '<h2>'+_('bannerx.stats.thismonth')+'</h2>'
            ,border: false
            ,cls: 'modx-page-header'
        },{
            xtype:'columnchart'
            ,url: Bannerx.config.baseUrl + 'manager/assets/ext3/resources/charts.swf'
            ,xField: 'name'
            ,yField: 'clicks'
            ,height: 200
            ,store: new Ext.data.JsonStore({
                url: Bannerx.config.connectorUrl
                ,baseParams: {
                    action: 'mgr/ads/getclicks'
                    ,period: '%Y-%m'
                }
                ,fields: ['name', 'clicks']
                ,autoLoad: true
                ,root: 'results'
            })
        },{
            html: '<h2>'+_('bannerx.stats.thisyear')+'</h2>'
            ,border: false
            ,cls: 'modx-page-header'
        },{
            xtype:'columnchart'
            ,url: Bannerx.config.baseUrl + 'manager/assets/ext3/resources/charts.swf'
            ,xField: 'name'
            ,yField: 'clicks'
            ,height: 200
            ,store: new Ext.data.JsonStore({
                url: Bannerx.config.connectorUrl
                ,baseParams: {
                    action: 'mgr/ads/getclicks'
                    ,period: '%Y'
                }
                ,fields: ['name', 'clicks']
                ,autoLoad: true
                ,root: 'results'
            })
        },{
            html: '<h2>'+_('bannerx.stats.overall')+'</h2>'
            ,border: false
            ,cls: 'modx-page-header'
        },{
            xtype:'columnchart'
            ,url: Bannerx.config.baseUrl + 'manager/assets/ext3/resources/charts.swf'
            ,xField: 'name'
            ,yField: 'clicks'
            ,height: 200
            ,store: new Ext.data.JsonStore({
                url: Bannerx.config.connectorUrl
                ,baseParams: {
                    action: 'mgr/ads/getclicks'
                    ,period: ''
                }
                ,fields: ['name', 'clicks']
                ,autoLoad: true
                ,root: 'results'
            })
        }]
    });
    Bannerx.panel.Stats.superclass.constructor.call(this,config);
};
Ext.extend(Bannerx.panel.Stats,MODx.Panel);
Ext.reg('bannerx-panel-stats',Bannerx.panel.Stats);
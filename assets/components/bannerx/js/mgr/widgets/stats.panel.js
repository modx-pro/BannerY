Bannerx.panel.Stats = function(config) {
    config = config || {};
    Ext.apply(config,{
        border: false
        ,baseCls: 'modx-panel'
        ,items: [{
            xtype: 'modx-tabs'
            ,bodyStyle: 'padding: 10px'
            ,defaults: { border: false ,autoHeight: true }
            ,items: [{
                title: _('bannerx.stats.clicks')
                ,items: [{
                            xtype: 'modx-combo'
                            ,id: 'bannerx-clicks-period'
                            ,mode: 'local'
                            ,store: new Ext.data.SimpleStore({
                                fields: ['d','v']
                                ,data: [[_('bannerx.stats.overall', '')],[_('bannerx.stats.today'),'%Y-%m-%d'],[_('bannerx.stats.thismonth'),'%Y-%m'],[_('bannerx.stats.lastmonth'),'last month'],[_('bannerx.stats.thisyear'),'%Y']]
                            })
                            ,displayField: 'd'
                            ,valueField: 'v'
                            ,lazyRender: false
                            ,listeners: {
                                added: {fn:function(){this.setValue('');}}
                                ,select: {fn:this.setPeriod,scope:this}
                            }
                        },{
                            xtype:'columnchart'
                            ,id: 'clickchart'
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

            },{
                title: _('bannerx.stats.referrers')
                ,items: [{
                            xtype: 'bannerx-grid-referrers'
                        }
                ]
            }]
        }]
    });
    Bannerx.panel.Stats.superclass.constructor.call(this,config);
};
Ext.extend(Bannerx.panel.Stats,MODx.Panel,{
     setPeriod: function(tf,nv,ov) {
        var el = Ext.getCmp('clickchart');
        var s = el.store;
        s.baseParams.period = tf.getValue();
        s.reload();
    }
});
Ext.reg('bannerx-panel-stats',Bannerx.panel.Stats);
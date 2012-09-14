Bannery.panel.Stats = function(config) {
    config = config || {};
    Ext.apply(config,{
        border: false
        ,baseCls: 'modx-panel'
        ,items: [{
            xtype: 'modx-tabs'
            ,bodyStyle: 'padding: 10px'
            ,id: 'bannery-stats-tabs'
            ,defaults: {autoHeight: true }
			,stateful: true
			,border: true
			,stateId: 'bannery-stats-tabs'
			,stateEvents: ['tabchange']
			,getState:function() {
				return { activeTab:this.items.indexOf(this.getActiveTab()) };
			}
            ,items: [{
                title: _('bannery.stats.clicks')
                ,items: [{
                            xtype: 'modx-combo'
                            ,id: 'bannery-clicks-period'
                            ,mode: 'local'
                            ,store: new Ext.data.SimpleStore({
                                fields: ['d','v']
                                ,data: [[_('bannery.stats.overall', '')],[_('bannery.stats.today'),'%Y-%m-%d'],[_('bannery.stats.thismonth'),'%Y-%m'],[_('bannery.stats.lastmonth'),'last month'],[_('bannery.stats.thisyear'),'%Y']]
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
                            ,url: Bannery.config.baseUrl + 'manager/assets/ext3/resources/charts.swf'
                            ,xField: 'name'
                            ,yField: 'clicks'
                            ,height: 200
                            ,store: new Ext.data.JsonStore({
                                url: Bannery.config.connectorUrl
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
                title: _('bannery.stats.referrers')
                ,items: [{
                            xtype: 'bannery-grid-referrers'
                        }
                ]
            }]
        }]
    });
    Bannery.panel.Stats.superclass.constructor.call(this,config);
};
Ext.extend(Bannery.panel.Stats,MODx.Panel,{
     setPeriod: function(tf,nv,ov) {
        var el = Ext.getCmp('clickchart');
        var s = el.store;
        s.baseParams.period = tf.getValue();
        s.reload();
    }
});
Ext.reg('bannery-panel-stats',Bannery.panel.Stats);

Bannery.grid.Referrers = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        id: 'bannery-grid-referrers'
        ,url: Bannery.config.connectorUrl
        ,baseParams: { action: 'mgr/clicks/getreferrers', period: ''}
        ,fields: ['referrer', 'clicks']
        ,paging: true
        ,border: false
        ,frame: false
        ,remoteSort: false
        ,anchor: '97%'
        ,autoExpandColumn: 'referrer'
        ,columns: [{
            header: _('bannery.stats.referrer')
            ,dataIndex: 'referrer'
            ,sortable: true
        },{
            header: _('bannery.stats.clicks')
            ,dataIndex: 'clicks'
            ,sortable: false
        }]
        ,tbar: [{
            xtype: 'modx-combo'
            ,id: 'bannery-referrers-period'
            ,mode: 'local'
            ,store: new Ext.data.SimpleStore({
                fields: ['d','v']
                ,data: [[_('bannery.stats.overall', '')],[_('bannery.stats.today'),'%Y-%m-%d'],[_('bannery.stats.thismonth'),'%Y-%m'],[_('bannery.stats.lastmonth'),'last month'],[_('bannery.stats.thisyear'),'%Y']]
            })
            ,displayField: 'd'
            ,valueField: 'v'
            ,lazyRender: false
            ,listeners: {
                'select': {fn:this.setPeriod,scope:this}
            }
        }]
    });
    Bannery.grid.Referrers.superclass.constructor.call(this,config)
};
Ext.extend(Bannery.grid.Referrers,MODx.grid.Grid,{
     setPeriod: function(tf,nv,ov) {
        var s = this.getStore();
        s.baseParams.period = tf.getValue();
        this.getBottomToolbar().changePage(1);
        this.refresh();
    }
});
Ext.reg('bannery-grid-referrers',Bannery.grid.Referrers);
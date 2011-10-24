Bannerx.grid.Referrers = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        id: 'bannerx-grid-referrers'
        ,url: Bannerx.config.connectorUrl
        ,baseParams: { action: 'mgr/clicks/getreferrers', period: ''}
        ,fields: ['referrer', 'clicks']
        ,paging: true
        ,border: false
        ,frame: false
        ,remoteSort: false
        ,anchor: '97%'
        ,autoExpandColumn: 'referrer'
        ,columns: [{
            header: _('bannerx.stats.referrer')
            ,dataIndex: 'referrer'
            ,sortable: true
        },{
            header: _('bannerx.stats.clicks')
            ,dataIndex: 'clicks'
            ,sortable: false
        }]
        ,tbar: [{
            xtype: 'modx-combo'
            ,id: 'bannerx-referrers-period'
            ,mode: 'local'
            ,store: new Ext.data.SimpleStore({
                fields: ['d','v']
                ,data: [[_('bannerx.stats.overall', '')],[_('bannerx.stats.today'),'%Y-%m-%d'],[_('bannerx.stats.thismonth'),'%Y-%m'],[_('bannerx.stats.lastmonth'),'last month'],[_('bannerx.stats.thisyear'),'%Y']]
            })
            ,displayField: 'd'
            ,valueField: 'v'
            ,lazyRender: false
            ,listeners: {
                'select': {fn:this.setPeriod,scope:this}
            }
        }]
    });
    Bannerx.grid.Referrers.superclass.constructor.call(this,config)
};
Ext.extend(Bannerx.grid.Referrers,MODx.grid.Grid,{
     setPeriod: function(tf,nv,ov) {
        var s = this.getStore();
        s.baseParams.period = tf.getValue();
        this.getBottomToolbar().changePage(1);
        this.refresh();
    }
});
Ext.reg('bannerx-grid-referrers',Bannerx.grid.Referrers);
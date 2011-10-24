Bannerx.grid.Referrers = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        id: 'bannerx-grid-referrers'
        ,url: Bannerx.config.connectorUrl
        ,baseParams: { action: 'mgr/clicks/getreferrers' }
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
    });
    Bannerx.grid.Referrers.superclass.constructor.call(this,config)
};
Ext.extend(Bannerx.grid.Referrers,MODx.grid.Grid);
Ext.reg('bannerx-grid-referrers',Bannerx.grid.Referrers);
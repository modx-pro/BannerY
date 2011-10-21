Bannerx.panel.Home = function(config) {
    config = config || {};
    Ext.apply(config,{
        border: false
        ,baseCls: 'modx-formpanel'
        ,items: [{
            html: '<h2>'+_('bannerx.desc')+'</h2>'
            ,border: false
            ,cls: 'modx-page-header'
        },{
            xtype: 'modx-tabs'
            ,bodyStyle: 'padding: 10px'
            ,defaults: { border: false ,autoHeight: true }
            ,items: [{
                title: _('bannerx.ads')
                ,border: false
                ,defaults: { autoHeight: true, border: false }
                ,items: [{
                    xtype: 'bannerx-grid-ads'
                    ,preventRender: true
                }]
            },{
                title: _('bannerx.positions')
                ,border: false
                ,defaults: { autoHeight: true, border: false }
                ,items: [{
                    xtype: 'bannerx-grid-positions'
                    ,preventRender: true
                }]
            },{
                title: _('bannerx.stats')
                ,border: false
                ,defaults: { autoHeight: true, border: false }
                ,items: [{
                    xtype: 'bannerx-panel-stats'
                    ,preventRender: true
                }]
            }]
        }]
    });
    Bannerx.panel.Home.superclass.constructor.call(this,config);
};
Ext.extend(Bannerx.panel.Home,MODx.Panel);
Ext.reg('bannerx-panel-home',Bannerx.panel.Home);

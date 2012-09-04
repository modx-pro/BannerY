Ext.onReady(function() {
	MODx.load({ xtype: 'bannerx-page-home'});
});

Bannerx.page.Home = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		components: [{
			xtype: 'bannerx-panel-home'
			,renderTo: 'bannerx-panel-home-div'
		}]
	});
	Bannerx.page.Home.superclass.constructor.call(this,config);
};
Ext.extend(Bannerx.page.Home,MODx.Component);
Ext.reg('bannerx-page-home',Bannerx.page.Home);
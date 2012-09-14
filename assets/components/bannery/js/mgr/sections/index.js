Ext.onReady(function() {
	MODx.load({ xtype: 'bannery-page-home'});
});

Bannery.page.Home = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		components: [{
			xtype: 'bannery-panel-home'
			,renderTo: 'bannery-panel-home-div'
		}]
	});
	Bannery.page.Home.superclass.constructor.call(this,config);
};
Ext.extend(Bannery.page.Home,MODx.Component);
Ext.reg('bannery-page-home',Bannery.page.Home);
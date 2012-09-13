Bannerx.panel.Home = function(config) {
	config = config || {};
	Ext.apply(config,{
		baseCls: 'modx-formpanel'
		,cls: 'container'
		,items: [{
			html: '<h2>'+_('bannerx.desc')+'</h2>'
			,border: false
			,cls: 'modx-page-header container'
		},{
			xtype: 'modx-tabs'
			,id: 'bannerx-tabs'
			,bodyStyle: 'padding: 10px'
			,defaults: {autoHeight: true }
			,stateful: true
			,border: true
			,stateId: 'bannerx-tabs'
			,stateEvents: ['tabchange']
			,getState:function() {
				return { activeTab:this.items.indexOf(this.getActiveTab()) };
			}
			,items: [{
				title: _('bannerx.ads')
				,id: 'bannerx-ads'
				,border: false
				,defaults: { autoHeight: true, border: false }
				,items: [{
					xtype: 'bannerx-grid-ads'
					,preventRender: true
				}]
			},{
				title: _('bannerx.positions')
				,id: 'bannerx-positions'
				,border: false
				,defaults: { autoHeight: true, border: false }
				,items: [{
					xtype: 'bannerx-grid-positions'
					,preventRender: true
				}]
			},{
				title: _('bannerx.stats')
				,id: 'bannerx-stats'
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









function renderGridImage(img, height) {
	if (height == '') {height = 50;}
	if (img.length > 0) {
		if (!/(jpg|jpeg|png|gif|bmp)$/.test(img)) {return img;}
		else if (/^(http|https)/.test(img)) {return '<img src="'+img+'" alt="" style="display:block;margin:auto;height:'+height+'px;" />'}
		else {return '<img src="'+MODx.config.connectors_url+'system/phpthumb.php?&src='+img+'&wctx=web&h='+height+'&zc=0&source=1" alt="" style="display:block;margin:auto;height:'+height+'px;" />'}
	}
	else {return '';}
}
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







// Search and combos
/******************************************************/
MODx.combo.positions = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		name: 'position'
		,hiddenName: 'position'
		,displayField: 'name'
		,valueField: 'id'
		,editable: true
		,fields: ['name','id']
		,pageSize: 10
		//,value: ''
		,emptyText: _('bannerx.ads.add')
		,url: Bannerx.config.connectorUrl
		,baseParams: {
			action: 'mgr/ads/getlist'
			,position: config.position || 0
			,mode: config.mode || 0
		}
	});
	MODx.combo.positions.superclass.constructor.call(this,config);
};
Ext.extend(MODx.combo.positions,MODx.combo.ComboBox);
Ext.reg('bannerx-filter-positions',MODx.combo.positions);


MODx.form.FilterByQuery = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		xtype: 'textfield'
		,emptyText: _('search')
		,width: 200
	});
	MODx.form.FilterByQuery.superclass.constructor.call(this,config);
};
Ext.extend(MODx.form.FilterByQuery,Ext.form.TextField);
Ext.reg('bannerx-filter-byquery',MODx.form.FilterByQuery);

MODx.form.FilterClear = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		xtype: 'button'
		,text: _('clear_filter')
	});
	MODx.form.FilterClear.superclass.constructor.call(this,config);
};
Ext.extend(MODx.form.FilterClear,Ext.Button);
Ext.reg('bannerx-filter-clear',MODx.form.FilterClear);


// Functions
/******************************************************/
function renderGridImage(img, height) {
	if (height == '') {height = 50;}
	if (img.length > 0) {
		if (!/(jpg|jpeg|png|gif|bmp)$/.test(img)) {return img;}
		else if (/^(http|https)/.test(img)) {return '<img src="'+img+'" alt="" style="display:block;margin:auto;height:'+height+'px;" />'}
		else {return '<img src="'+MODx.config.connectors_url+'system/phpthumb.php?&src='+img+'&wctx=web&h='+height+'&zc=0&source=1" alt="" style="display:block;margin:auto;height:'+height+'px;" />'}
	}
	else {return '';}
}
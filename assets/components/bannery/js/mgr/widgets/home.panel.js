Bannery.panel.Home = function(config) {
	config = config || {};
	Ext.apply(config,{
		baseCls: 'modx-formpanel'
		,cls: 'container'
		,items: [{
			html: '<h2>'+_('bannery.desc')+'</h2>'
			,border: false
			,cls: 'modx-page-header container'
		},{
			xtype: 'modx-tabs'
			,id: 'bannery-tabs'
			,bodyStyle: 'padding: 10px'
			,defaults: {autoHeight: true }
			,stateful: true
			,border: true
			,stateId: 'bannery-tabs'
			,stateEvents: ['tabchange']
			,getState:function() {
				return { activeTab:this.items.indexOf(this.getActiveTab()) };
			}
			,items: [{
				title: _('bannery.ads')
				,id: 'bannery-ads'
				,border: false
				,defaults: { autoHeight: true, border: false }
				,items: [{
					xtype: 'bannery-grid-ads'
					,preventRender: true
				}]
			},{
				title: _('bannery.positions')
				,id: 'bannery-positions'
				,border: false
				,defaults: { autoHeight: true, border: false }
				,items: [{
					xtype: 'bannery-grid-positions'
					,preventRender: true
				}]
			},{
				title: _('bannery.stats')
				,id: 'bannery-stats'
				,border: false
				,defaults: { autoHeight: true, border: false }
				,items: [{
					xtype: 'bannery-panel-stats'
					,preventRender: true
				}]
			}]
		}]
	});
	Bannery.panel.Home.superclass.constructor.call(this,config);
};
Ext.extend(Bannery.panel.Home,MODx.Panel);
Ext.reg('bannery-panel-home',Bannery.panel.Home);







// Search and combos
/******************************************************/
MODx.combo.ads = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		name: 'ad'
		,hiddenName: 'ad'
		,displayField: 'name'
		,valueField: 'id'
		,editable: true
		,fields: ['name','id']
		,pageSize: 10
		//,value: ''
		,emptyText: _('bannery.ads.add')
		,url: Bannery.config.connectorUrl
		,baseParams: {
			action: 'mgr/ads/getlist'
			,position: config.position || 0
			,mode: config.mode || 0
		}
	});
	MODx.combo.ads.superclass.constructor.call(this,config);
};
Ext.extend(MODx.combo.ads,MODx.combo.ComboBox);
Ext.reg('bannery-filter-ads',MODx.combo.ads);

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
		,emptyText: _('bannery.positions.select')
		,url: Bannery.config.connectorUrl
		,baseParams: {
			action: 'mgr/positions/getlist'
		}
	});
	MODx.combo.positions.superclass.constructor.call(this,config);
};
Ext.extend(MODx.combo.positions,MODx.combo.ComboBox);
Ext.reg('bannery-filter-positions',MODx.combo.positions);


MODx.combo.resources = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		name: 'url'
		,hiddenName: 'url'
		,displayField: 'pagetitle'
		,valueField: 'url'
		,editable: true
		,fields: ['pagetitle','url']
		,pageSize: 10
		,emptyText: ''
		,url: Bannery.config.connectorUrl
		,baseParams: {
			action: 'mgr/resource/getlist'
		}
	});
	MODx.combo.resources.superclass.constructor.call(this,config);
};
Ext.extend(MODx.combo.resources,MODx.combo.ComboBox);
Ext.reg('bannery-filter-resources',MODx.combo.resources);

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
Ext.reg('bannery-filter-byquery',MODx.form.FilterByQuery);

MODx.form.FilterClear = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		xtype: 'button'
		,text: _('clear_filter')
	});
	MODx.form.FilterClear.superclass.constructor.call(this,config);
};
Ext.extend(MODx.form.FilterClear,Ext.Button);
Ext.reg('bannery-filter-clear',MODx.form.FilterClear);


// Functions
/******************************************************/
function renderGridImage(img, height) {
	if (height == '') {height = 50;}
	if (img.length > 0) {
		if (!/(jpg|jpeg|png|gif|bmp)$/.test(img)) {return img;}
		else if (/^(http|https)/.test(img)) {return '<img src="'+img+'" alt="" style="display:block;margin:auto;height:'+height+'px;" />'}
		else {return '<img src="'+MODx.config.connectors_url+'system/phpthumb.php?&src='+img+'&wctx=web&h='+height+'&zc=0&source='+Bannery.config.media_source+'" alt="" style="display:block;margin:auto;height:'+height+'px;" />'}
	}
	else {return '';}
}
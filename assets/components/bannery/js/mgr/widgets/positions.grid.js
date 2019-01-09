Bannery.grid.Positions = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		id: 'bannery-grid-positions'
		,url: Bannery.config.connectorUrl
		,baseParams: { action: 'mgr/positions/getlist' }
		,fields: ['id','name','clicks']
		,border: false
		,remoteSort: true
		,paging: true
		,columns: [{
			header: _('id')
			,dataIndex: 'id'
			,sortable: true
			,width: 10
		},{
			header: _('bannery.positions.name')
			,dataIndex: 'name'
			,sortable: true
		},{
			header: _('bannery.positions.clicks')
			,dataIndex: 'clicks'
			,sortable: false
		}]
		,tbar: [{
			text: _('bannery.positions.new')
			,handler: this.createPosition
		},{
			xtype: 'tbfill'
		},{
			xtype: 'bannery-filter-byquery'
			,id: 'bannery-positions-filter-byquery'
			,listeners: {
				render: {fn: function(tf) {tf.getEl().addKeyListener(Ext.EventObject.ENTER, function() {this.FilterByQuery(tf);}, this);},scope: this}
			}
		},{
			xtype: 'bannery-filter-clear'
			,text: '<i class="icon icon-times"></i>'
			,listeners: {click: {fn: this.FilterClear, scope: this}}
		}]
		,listeners: {
			rowDblClick: function(grid, rowIndex, e) {
				var row = grid.store.getAt(rowIndex);
				this.updatePosition(grid, e, row);
			}
		}
	});
	Bannery.grid.Positions.superclass.constructor.call(this,config)
};
Ext.extend(Bannery.grid.Positions,MODx.grid.Grid,{
	getMenu: function() {
		var icon = 'x-menu-item-icon icon icon-';
		var m = [{
				text: '<i class="' + icon + 'edit"></i>' + _('bannery.positions.update')
				,handler: this.updatePosition
			},'-',{
			text: '<i class="' + icon + 'times"></i>' + _('bannery.positions.remove')
				,handler: this.removePosition
			}];
		this.addContextMenuItem(m);
		return true;
	}
	,FilterClear: function() {
		var s = this.getStore();
		s.baseParams.query = '';
		Ext.getCmp('bannery-positions-filter-byquery').reset();
		this.getBottomToolbar().changePage(1);
		this.refresh();
	}
	,FilterByQuery: function(tf, nv, ov) {
		var s = this.getStore();
		s.baseParams.query = tf.getValue();
		this.getBottomToolbar().changePage(1);
		this.refresh();
	}
	,createPosition: function(btn,e) {
		var w = MODx.load({
			xtype: 'bannery-window-position'
			,update: 0
			,position: 0
			,closeAction: 'close'
			,listeners: {
				success:{fn:function() {
					Ext.getCmp('bannery-grid-positions').store.reload();
					Bannery.posStore.reload();
				},scope:this}
			}
			,baseParams: {
				action: 'mgr/positions/create'
			}
		});
		w.setTitle(_('bannery.positions.new')).show(e.target,function() {w.setPosition(null,50)},this);
		Ext.getCmp('bannery-window-position').reset();
	}
	,updatePosition: function(btn,e, row) {
		if (typeof(row) != 'undefined') {this.menu.record = row.data;}
		var w = MODx.load({
			xtype: 'bannery-window-position'
			,update: 1
			,position: this.menu.record.id
			,closeAction: 'close'
			,listeners: {
				'success':{fn:function() {
					Ext.getCmp('bannery-grid-positions').store.reload();
					Bannery.posStore.reload();
				},scope:this}
			}
		});
		w.setTitle(_('bannery.positions.update')).show(e.target,function() {w.setPosition(null,50)},this);
		Ext.getCmp('bannery-window-position').reset();
		Ext.getCmp('bannery-window-position').setValues(this.menu.record);
	}
	,removePosition: function() {
		MODx.msg.confirm({
			title: _('bannery.positions.remove')
			,text: _('bannery.positions.remove.confirm')
			,url: this.config.url
			,params: {
				action: 'mgr/positions/remove'
				,id: this.menu.record.id
			}
			,listeners: {
				'success':{fn:function() {
					Ext.getCmp('bannery-grid-positions').store.reload();
					Bannery.posStore.reload();
				},scope:this}
			}
		});
	}
});
Ext.reg('bannery-grid-positions',Bannery.grid.Positions);

Bannery.window.Position = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		id: 'bannery-window-position'
		,title: _('bannery.positions.new')
		,url: Bannery.config.connectorUrl
		,modal: true
		,width: 600
		,autoHeight: true
		,baseParams: {
			action: 'mgr/positions/update'
		}
		,fields: [{
				xtype: 'hidden'
				,name: 'id'
			},{
				xtype: 'textfield'
				,fieldLabel: _('bannery.positions.name')
				,name: 'name'
				,anchor: '99%'
				,allowBlank: false
			},{
				xtype: 'bannery-grid-adpositions'
				,update: config.update
				,position: config.position
				,pageSize: 5
			}
		]
		,keys: [{key: Ext.EventObject.ENTER,shift: true,fn:  function() {this.submit()},scope: this}]
	});
	Bannery.window.Position.superclass.constructor.call(this,config);
};
Ext.extend(Bannery.window.Position,MODx.Window);
Ext.reg('bannery-window-position',Bannery.window.Position);



Bannery.grid.AdPositions = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		id: 'bannery-grid-adpositions'
		,url: Bannery.config.connectorUrl
		,baseParams: {
			action: 'mgr/adpositions/getlist'
			,position: config.position || 0
		}
		,fields: ['id','name','idx','image']
		,autoHeight: true
		,paging: true
		,disabled: config.update == 0 ? 1 : 0
		,hidden: config.update == 0 ? 1 : 0
		,pageSize: config.pageSize || 5
		,columns: [
			{header: _('bannery.adposition.idx'),dataIndex: 'idx',sortable: false, width: 25}
			,{header: _('bannery.ads.name'),dataIndex: 'name',sortable: false}
			,{header: _('bannery.ads.image'),dataIndex: 'image',sortable: false, width: 50, renderer: {fn:function(img) {return Bannery.renderGridImage(img)}}, id: 'byad-thumb2'}
		]
		,plugins: [new Ext.ux.dd.GridDragDropRowOrder({
			listeners: {
				'afterrowmove': {
					fn: function(drag, old_order, new_order, row) {
						row = row[0];
						var grid = drag.grid;
						var el = Ext.get('bannery-grid-adpositions');
						el.mask(_('loading'),'x-mask-loading')
						MODx.Ajax.request({
							url: Bannery.config.connectorUrl
							,params: {
								action: 'mgr/adpositions/sort'
								,id: row.data.id
								,new_order: new_order
								,old_order: old_order
							}
							,listeners: {
								'success': {fn:function(r) {
									el.unmask();
									grid.refresh();
								},scope:grid}
								,'failure': {fn:function(r) {
									el.unmask();
								},scope:grid}
							}
						})
					}
					,scope: this
				}
			}
		})]
		,tbar: [{
			xtype: 'bannery-filter-ads'
			,id: 'bannery-grid-adpositions-adsfilter'
			,position: config.position
			,mode: 'exclude'
			,width: 250
			,listeners: {
				'select': {fn:function(combo,row,idx) {
					this.addAdPosition(row.id, config.position, combo)
					combo.clearValue();
				}, scope:this}
			}
		}]
	});
	Bannery.grid.AdPositions.superclass.constructor.call(this,config)
};
Ext.extend(Bannery.grid.AdPositions,MODx.grid.Grid,{
	getMenu: function() {
		var icon = 'x-menu-item-icon icon icon-';
		var m = [{
				text: '<i class="' + icon + 'times"></i> ' + _('bannery.adposition.remove')
				,handler: this.removeAdPosition
			}];
		this.addContextMenuItem(m);
		return true;
	}
	,removeAdPosition: function() {
		MODx.Ajax.request({
			url: Bannery.config.connectorUrl
			,params: {
				action: 'mgr/adpositions/remove'
				,id: this.menu.record.id
			}
			,listeners: {
				'success': {fn:function() {
					this.refresh();
					Ext.getCmp('bannery-grid-adpositions-adsfilter').store.reload();
				},scope:this}
			}
		});
	}
	,addAdPosition: function(ad, position, combo) {
		MODx.Ajax.request({
			url: Bannery.config.connectorUrl
			,params: {
				action: 'mgr/adpositions/add'
				,ad: ad
				,position: position
			}
			,listeners: {
				'success': {fn:function() {
					this.refresh();
					combo.store.reload();
				},scope:this}
			}
		})
	}
});
Ext.reg('bannery-grid-adpositions',Bannery.grid.AdPositions);
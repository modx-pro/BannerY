Bannerx.grid.Positions = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		id: 'bannerx-grid-positions'
		,url: Bannerx.config.connectorUrl
		,baseParams: { action: 'mgr/positions/getlist' }
		,fields: ['id','name','clicks']
		,paging: true
		,border: false
		,frame: false
		,remoteSort: true
		,anchor: '97%'
		,autoExpandColumn: 'name'
		,columns: [{
			header: _('id')
			,dataIndex: 'id'
			,sortable: true
			,width: 10
		},{
			header: _('bannerx.positions.name')
			,dataIndex: 'name'
			,sortable: true
		},{
			header: _('bannerx.positions.clicks')
			,dataIndex: 'clicks'
			,sortable: false
		}]
		,tbar: [{
			text: _('bannerx.positions.new')
			,handler: this.createPosition
		}]
		,listeners: {
			rowDblClick: function(grid, rowIndex, e) {
				var row = grid.store.getAt(rowIndex);
				this.updatePosition(grid, e, row);
			}
		}
	});
	Bannerx.grid.Positions.superclass.constructor.call(this,config)
};
Ext.extend(Bannerx.grid.Positions,MODx.grid.Grid,{
	getMenu: function() {
		var m = [{
				text: _('bannerx.positions.update')
				,handler: this.updatePosition
			},'-',{
				text: _('bannerx.positions.remove')
				,handler: this.removePosition
			}];
		this.addContextMenuItem(m);
		return true;
	}
	,createPosition: function(btn,e) {
		w = MODx.load({
			xtype: 'bannerx-window-position'
			,update: 0
			,position: 0
			,listeners: {
				'success':{fn:function() {
					Ext.getCmp('bannerx-grid-positions').store.reload();
					Bannerx.posStore.reload();
				},scope:this}
				,'hide': {fn:function() {
					this.getEl().remove();
					//this.destroy();
				}}
			}
			,baseParams: {
				action: 'mgr/positions/create'
			}
		});
		w.setTitle(_('bannerx.positions.new')).show(e.target,function() {w.setPosition(null,50)},this);
		Ext.getCmp('bannerx-window-position').reset();
	}
	,updatePosition: function(btn,e, row) {
		if (typeof(row) != 'undefined') {this.menu.record = row.data;}
		w = MODx.load({
			xtype: 'bannerx-window-position'
			,update: 1
			,position: this.menu.record.id
			,listeners: {
				'success':{fn:function() {
					Ext.getCmp('bannerx-grid-positions').store.reload();
					Bannerx.posStore.reload();
				},scope:this}
				,'hide': {fn:function() {
					this.getEl().remove();
					//this.destroy();
				}}
			}
		});
		w.setTitle(_('bannerx.positions.update')).show(e.target,function() {w.setPosition(null,50)},this);
		Ext.getCmp('bannerx-window-position').reset();
		Ext.getCmp('bannerx-window-position').setValues(this.menu.record);
	}
	,removePosition: function() {
		MODx.msg.confirm({
			title: _('bannerx.positions.remove')
			,text: _('bannerx.positions.remove.confirm')
			,url: this.config.url
			,params: {
				action: 'mgr/positions/remove'
				,id: this.menu.record.id
			}
			,listeners: {
				'success':{fn:function() {
					Ext.getCmp('bannerx-grid-positions').store.reload();
					Bannerx.posStore.reload();
				},scope:this}
			}
		});
	}
});
Ext.reg('bannerx-grid-positions',Bannerx.grid.Positions);

Bannerx.window.Position = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		id: 'bannerx-window-position'
		,title: _('bannerx.positions.new')
		,url: Bannerx.config.connectorUrl
		,modal: true
		,baseParams: {
			action: 'mgr/positions/update'
		}
		,fields: [{
				xtype: 'hidden'
				,name: 'id'
			},{
				xtype: 'textfield'
				,fieldLabel: _('bannerx.positions.name')
				,name: 'name'
				,anchor: '99%'
				,allowBlank: false
			},{
				xtype: 'bannerx-grid-adpositions'
				,update: config.update
				,position: config.position
			}
		]
		,keys: [{
			key: Ext.EventObject.ENTER
			,shift: false
			,fn:  function() {this.submit()}
			,scope: this
		}]
	});
	Bannerx.window.Position.superclass.constructor.call(this,config);
};
Ext.extend(Bannerx.window.Position,MODx.Window);
Ext.reg('bannerx-window-position',Bannerx.window.Position);



Bannerx.grid.AdPositions = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		id: 'bannerx-grid-adpositions'
		,url: Bannerx.config.connectorUrl
		,baseParams: {
			action: 'mgr/adpositions/getlist'
			,position: config.position || 0
		}
		,fields: ['id','name','idx','image']
		,paging: true
		,anchor: '99%'
		,disabled: config.update == 0 ? 1 : 0
		,hidden: config.update == 0 ? 1 : 0
		,pageSize: Math.round(MODx.config.default_per_page / 3)
		,columns: [
			{header: _('bannerx.adposition.idx'),dataIndex: 'idx',sortable: false, width: 25}
			,{header: _('bannerx.ads.name'),dataIndex: 'name',sortable: false}
			,{header: _('bannerx.ads.image'),dataIndex: 'image',sortable: false, width: 50, renderer: {fn:function(img) {return renderGridImage(img,30)}}}
		]
		,plugins: [new Ext.ux.dd.GridDragDropRowOrder({
			listeners: {
				'afterrowmove': {
					fn: function(drag, old_order, new_order, row) {
						var row = row[0];
						var grid = drag.grid;
						var el = Ext.get('bannerx-grid-adpositions');
						el.mask(_('loading'),'x-mask-loading')
						MODx.Ajax.request({
							url: Bannerx.config.connectorUrl
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
		/*
		,tbar: [{
			text: _('bannerx.positions.new')
			,handler: this.createPosition
		}]
		,listeners: {
			rowDblClick: function(grid, rowIndex, e) {
				var row = grid.store.getAt(rowIndex);
				this.updatePosition(grid, e, row);
			}
		}
		*/
	});
	Bannerx.grid.AdPositions.superclass.constructor.call(this,config)
};
Ext.extend(Bannerx.grid.AdPositions,MODx.grid.Grid,{
	getMenu: function() {
		var m = [{
				text: _('bannerx.adposition.remove')
				,handler: this.removeAdPosition
			}];
		this.addContextMenuItem(m);
		return true;
	}
	,removeAdPosition: function() {
		MODx.msg.confirm({
			title: _('bannerx.adposition.remove')
			,text: _('bannerx.adposition.remove.confirm')
			,url: this.config.url
			,params: {
				action: 'mgr/adpositions/remove'
				,id: this.menu.record.id
			}
			,listeners: {
				'success': {fn:this.refresh,scope:this}
			}
		});
	}
	/*
	getMenu: function() {
		var m = [{
				text: _('bannerx.positions.update')
				,handler: this.updatePosition
			},'-',{
				text: _('bannerx.positions.remove')
				,handler: this.removePosition
			}];
		this.addContextMenuItem(m);
		return true;
	}
	*/
});
Ext.reg('bannerx-grid-adpositions',Bannerx.grid.AdPositions);
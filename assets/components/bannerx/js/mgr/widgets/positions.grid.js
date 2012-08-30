Bannerx.grid.Positions = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		id: 'bannerx-grid-positions'
		,url: Bannerx.config.connectorUrl
		,baseParams: { action: 'mgr/positions/getlist' }
		,fields: ['id','name', 'clicks']
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
		if (!this.PositionWindow) {
			this.PositionWindow = MODx.load({
				xtype: 'bannerx-window-position'
				,listeners: {
					'success':{fn:function() {
						Ext.getCmp('bannerx-grid-positions').store.reload();
						Bannerx.posStore.reload();
					},scope:this}
				}
			});
		}
		this.PositionWindow.show(e.target);
		this.PositionWindow.setTitle(_('bannerx.positions.new'));
		Ext.getCmp('bannerx-window-position').reset();
	}
	,updatePosition: function(btn,e, row) {
		if (typeof(row) != 'undefined') {this.menu.record = row.data;}
		if (!this.PositionWindow) {
			this.PositionWindow = MODx.load({
				xtype: 'bannerx-window-position'
				,listeners: {
					'success':{fn:function() {
						Ext.getCmp('bannerx-grid-positions').store.reload();
						Bannerx.posStore.reload();
					},scope:this}
				}
			});
		}
		this.PositionWindow.show(e.target);
		this.PositionWindow.setTitle(_('bannerx.positions.update'));
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
				,width: 300
				,allowBlank: false
			}
		]
		,keys: [{
			key: Ext.EventObject.ENTER
			,shift: true
			,fn:  function() {this.submit() }
			,scope: this
		}]
	});
	Bannerx.window.Position.superclass.constructor.call(this,config);
};
Ext.extend(Bannerx.window.Position,MODx.Window);
Ext.reg('bannerx-window-position',Bannerx.window.Position);

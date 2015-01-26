Bannery.grid.Ads = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		id: 'bannery-grid-ads'
		,url: Bannery.config.connectorUrl
		,baseParams: { action: 'mgr/ads/getlist' }
		,fields: ['id','name', 'url', 'image', 'current_image', 'active', 'positions', 'clicks', 'start', 'end', 'description']
		,border: false
		,remoteSort: true
		,paging: true
		,columns: [
			{header: _('id'),dataIndex: 'id',sortable: true,width: 10}
			,{header: _('bannery.ads.name'),dataIndex: 'name',sortable: true, width: 75}
			,{header: _('bannery.ads.url'),dataIndex: 'url',sortable: true, width: 100}
			,{header: _('bannery.ads.clicks'),dataIndex: 'clicks',sortable: false, width: 50}
			,{header: _('bannery.ads.active'),dataIndex: 'active',sortable: true, renderer: this.renderBoolean, width: 50}
			,{header: _('bannery.ads.image'),dataIndex: 'current_image',sortable: false,renderer: {fn:function(img) {return Bannery.renderGridImage(img)}}, id: "byad-thumb", width: 100}
			,{header: _('bannery.ads.start'),dataIndex: 'start',sortable: true, width: 75}
			,{header: _('bannery.ads.end'),dataIndex: 'end',sortable: true, width: 75}
			//,{header: _('bannery.ads.description'),dataIndex: 'description',sortable: false, hidden: true}
		]
		,tbar: [{
			text: _('bannery.ads.new')
			,handler: this.createAd
		},{
			xtype: 'tbfill'
		},{
			xtype: 'bannery-filter-positions'
			,id: 'bannery-grid-ads-positionsfilter'
			,width: 200
			,listeners: {'select': {fn: this.FilterByPosition, scope:this}}
		},{
			xtype: 'tbspacer'
			,width: 10
		}, {
			xtype: 'bannery-filter-byquery'
			,id: 'bannery-ads-filter-byquery'
			,listeners: {
				render: {fn: function(tf) {tf.getEl().addKeyListener(Ext.EventObject.ENTER, function() {this.FilterByQuery(tf);}, this);},scope: this}
			}
		},{
			xtype: 'bannery-filter-clear'
			,text: '<i class="'+ (MODx.modx23 ? 'icon icon-times' : 'fa fa-times') + '"></i>'
			,listeners: {click: {fn: this.FilterClear, scope: this}}
		}]
		,viewConfig: {
			forceFit: true
			,enableRowBody: true
			,autoFill: true
			,showPreview: true
			,scrollOffset: 0
			,getRowClass : function(rec, ri, p) {
				if (!rec.data.active) {
					return 'bannery-row-disabled';
				}
				return '';
			}
		}
		,listeners: {
			rowDblClick: function(grid, rowIndex, e) {
				var row = grid.store.getAt(rowIndex);
				this.updateAd(grid, e, row);
			}
		}
	});

	//positions store/array for checkboxes in add/update window
	Bannery.positionsArray = [];
	Bannery.posStore = new Ext.data.JsonStore({
	url: Bannery.config.connectorUrl
		,root: 'results'
		,baseParams: { action: 'mgr/positions/getlist', limit : 0 }
		,fields: ["id", "name"]
		,autoLoad: true
		,listeners: {
			load: function(t, records, options) {
				Bannery.positionsArray = [];
				for (var i=0; i<records.length; i++) {
					Bannery.positionsArray.push({name: "positions[]", inputValue: records[i].data.id, boxLabel: records[i].data.name});
				}
			}
		}
	});

	Bannery.grid.Ads.superclass.constructor.call(this,config);
};
Ext.extend(Bannery.grid.Ads,MODx.grid.Grid,{
	getMenu: function(grid,idx) {
		var icon = MODx.modx23
			? 'x-menu-item-icon icon icon-'
			: 'x-menu-item-icon fa fa-';
		var row = grid.store.data.items[idx]
		var m = [{
			text: '<i class="' + icon + 'edit"></i> ' + _('bannery.ads.update')
			,handler: this.updateAd
		}];
		if (row.data.active == 0) {
			m.push({
					text: '<i class="' + icon + 'check"></i> ' +  _('bannery.ads.enable')
				,handler: this.enableAd}
			);
		}
		else {
			m.push({
				text: '<i class="' + icon + 'power-off"></i> ' + _('bannery.ads.disable')
				,handler: this.disableAd
			});
		}
		m.push('-', {
			text: '<i class="' + icon + 'times"></i> ' + _('bannery.ads.remove')
			,handler: this.removeAd
		});
		this.addContextMenuItem(m);
		return true;
	}
	,FilterClear: function() {
		var s = this.getStore();
		s.baseParams.query = '';
		s.baseParams.position = '';
		Ext.getCmp('bannery-ads-filter-byquery').reset();
		Ext.getCmp('bannery-grid-ads-positionsfilter').reset();
		this.getBottomToolbar().changePage(1);
		this.refresh();
	}
	,FilterByQuery: function(tf, nv, ov) {
		var s = this.getStore();
		s.baseParams.query = tf.getValue();
		this.getBottomToolbar().changePage(1);
		this.refresh();
	}
	,FilterByPosition: function(combo, row, idx) {
		var s = this.getStore();
		s.baseParams.position = row.id;
		this.getBottomToolbar().changePage(1);
		this.refresh();
	}
	,createAd: function(btn,e) {
		if (Bannery.positionsArray.length == 0) {
			MODx.msg.alert(_('error'),_('bannery.error.no_positions'));
			Ext.getCmp('bannery-tabs').setActiveTab('bannery-positions');
			return;
		}
		var w = MODx.load({
			xtype: 'bannery-window-ad'
			,update: 0
			,openTo: '/'
			,closeAction: 'close'
			,baseParams: {
				action: 'mgr/ads/create'
			}
			,listeners: {
				success: {fn:this.refresh,scope:this}
				//,hide: {fn:function() {this.getEl().remove()}}
			}
		});
		w.setTitle(_('bannery.ads.new')).show(e.target,function() {w.setPosition(null,50)},this);
		Ext.getCmp('bannery-window-ad').reset();
		Ext.getCmp('currimg').setSrc('');
	}
	,updateAd: function(btn,e, row) {
		if (typeof(row) != 'undefined') {this.menu.record = row.data;}
		if (Bannery.positionsArray.length == 0) {
			MODx.msg.alert(_('error'),_('bannery.error.no_positions'));
			Ext.getCmp('bannery-tabs').setActiveTab('bannery-positions');
			return;
		}
		var openTo = this.menu.record.image;
		if (openTo != '' && typeof openTo !== "undefined") {
			if (!/^\//.test(openTo)) {
				openTo = '/' + openTo;
			}
			if (!/$\//.test(openTo)) {
				var tmp = openTo.split('/')
				delete tmp[tmp.length - 1];
				tmp = tmp.join('/');
				openTo = tmp.substr(1)
			}
		}

		MODx.Ajax.request({
			url: Bannery.config.connectorUrl
			,params: {
				action: 'mgr/ads/get'
				,id: this.menu.record.id
			}
			,listeners: {
				'success': {fn:function(r) {
					var record = r.object;

					var w = MODx.load({
						xtype: 'bannery-window-ad'
						,update: 1
						,openTo: openTo
						,record: record
						,closeAction: 'close'
						,listeners: {
							success: {fn:this.refresh,scope:this}
							//,hide: {fn:function() {this.getEl().remove()}}
						}
					});

					record.newimage = record.image;
					w.reset();
					w.setValues(record);
					w.setTitle(_('bannery.ads.update')).show(e.target,function() {w.setPosition(null,50)},this);
					Ext.getCmp('currimg').setSrc(record.current_image);
					this.enablePositions(record.positions);
				},scope:this}
			}
		});
	}
	,removeAd: function() {
		MODx.msg.confirm({
			title: _('bannery.ads.remove')
			,text: _('bannery.ads.remove.confirm')
			,url: this.config.url
			,params: {
				action: 'mgr/ads/remove'
				,id: this.menu.record.id
			}
			,listeners: {
				'success': {fn:this.refresh,scope:this}
			}
		});
	}
	,enableAd: function() {
		MODx.Ajax.request({
			url: Bannery.config.connectorUrl
			,params: {
				action: 'mgr/ads/enable'
				,id: this.menu.record.id
			}
			,listeners: {
				'success': {fn:this.refresh,scope:this}
			}
		})
	}
	,disableAd: function() {
		MODx.Ajax.request({
			url: Bannery.config.connectorUrl
			,params: {
				action: 'mgr/ads/disable'
				,id: this.menu.record.id
			}
			,listeners: {
				'success': {fn:this.refresh,scope:this}
			}
		})
	}
	,enablePositions: function(positions) {
		var checkboxgroup = Ext.getCmp('positions');
		Ext.each(checkboxgroup.items.items, function(item) {
			if( positions.indexOf(item.inputValue) !== -1) {
				item.setValue(true);
			}
			else {
				item.setValue(false);
			}
		});
	}
	,renderBoolean: function(value) {
		if (value == 1) {return '<span style="color:green;">'+_('yes')+'</span>';}
		else {return '<span style="color:red;">'+_('no')+'</span>';}
	}
});
Ext.reg('bannery-grid-ads',Bannery.grid.Ads);

Bannery.window.Ad = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		id: 'bannery-window-ad'
		,title: _('bannery.ads.new')
		,url: Bannery.config.connectorUrl
		//,fileUpload: true
		,modal: true
		,resizable: false
		,maximizable: false
		,autoHeight: true
		,width: 600
		,baseParams: {
			action: 'mgr/ads/update'
		}
		,fields: [{
				xtype: 'hidden'
				,name: 'id'
			},{
				xtype: 'hidden'
				,name: 'image'
				,anchor: '99%'
				,id: 'image'
			},{
				xtype: 'textfield'
				,fieldLabel: _('bannery.ads.name')
				,name: 'name'
				,anchor: '99%'
				,allowBlank: false
			},{
				items: [{
					layout: 'form'
					,items: [{
						layout: 'column'
						,border: false
						,items: [{
							columnWidth: .8
							,border: false
							,layout: 'form'
							,items: [{
								xtype: 'modx-combo-source'
								,fieldLabel: _('bannery.ads.source')
								,id: 'modx-combo-source'
								,name: 'source'
								,anchor: '100%'
								,value: config.record ? config.record.source : MODx.config.default_media_source
							}]
						},{
							columnWidth: .2
							,border: false
							,layout: 'form'
							,items: [{
								xtype: 'xcheckbox'
								,fieldLabel: _('bannery.ads.active')
								,name: 'active'
								,inputValue: 1
								,checked: !config.update
							}]
						}]
					}]
				}]
			},{
				items: [{
					layout: 'column'
					,border: false
					,items: [{
						columnWidth: .3
						,border: false
						,layout: 'form'
						,items: [{
							id: 'currimg'
							//,hideLabel: true
							,style: 'margin-top: 20px;'
							,xtype: 'image'
							,cls: 'bannery-thumb-window'
						}]
					},{
						columnWidth: .7
						,border: false
						,layout: 'form'
						,style: 'margin-right: 5px;'
						,items: [{
							xtype: 'modx-combo-adbrowser'
							,fieldLabel: config.update ? _('bannery.ads.image.current') : _('bannery.ads.image.new')
							,name: 'newimage'
							,hideFiles: true
							,anchor: '99%'
							,allowBlank: true
							,openTo: config.openTo || '/'
							,listeners: {
								select: {fn:function(data) {
                                    Ext.getCmp('currimg').setSrc(data.url, Ext.getCmp('modx-combo-source').getValue());
									Ext.getCmp('image').setValue(data.relativeUrl);
								}}
								,change: {fn:function(data) {
									var value = this.getValue();
									Ext.getCmp('currimg').setSrc(value, Ext.getCmp('modx-combo-source').getValue());
									Ext.getCmp('image').setValue(value);
								}}
							}
						},{
							xtype: 'bannery-filter-resources'
							,fieldLabel: _('bannery.ads.url')
							,name: 'url'
							,description: _('bannery.ads.url.description')
							,anchor: '99%'
							,allowBlank: true
						},{
							xtype: 'textarea'
							,fieldLabel: _('bannery.ads.description')
							,name: 'description'
							,anchor: '99%'
							,height: 75
							,allowBlank: true
							,resize: true
						}]
					}]
				}]
			},{
				items: [{
					layout: 'form'
					,items: [{
						layout: 'column'
						,border: false
						,items: [{
							columnWidth: .5
							,border: false
							,layout: 'form'
							,items: [{
								xtype : 'xdatetime'
								,fieldLabel: _('bannery.ads.start')
								,name: 'start'
								,dateFormat: 'Y-m-d'
								,allowBlank: true
								,timeFormat: 'H:i'
								,emptyText: null
								,anchor: '99%'
							}]
						},{
							columnWidth: .5
							,border: false
							,layout: 'form'
							,style: 'margin-right: 5px;'
							,items: [{
								xtype : 'xdatetime'
								,fieldLabel: _('bannery.ads.end')
								,name: 'end'
								,allowBlank: true
								,dateFormat: 'Y-m-d'
								,timeFormat: 'H:i'
								,emptyText: null
								,anchor: '99%'
							}]
						}]
					}]
				}]
			},{
				xtype: 'checkboxgroup'
				,id: 'positions'
				,columns: 3
				,items: Bannery.positionsArray
				,fieldLabel: _('bannery.positions')
				,name: 'positions'
			}
		]
		,keys: [{key: Ext.EventObject.ENTER,shift: true,fn:  function() {this.submit()},scope: this}]
	});
	Bannery.window.Ad.superclass.constructor.call(this,config);
};
Ext.extend(Bannery.window.Ad,MODx.Window);
Ext.reg('bannery-window-ad',Bannery.window.Ad);

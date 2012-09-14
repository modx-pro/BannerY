Ext.BLANK_IMAGE_URL = '/assets/components/bannerx/img/_blank.png'

Ext.ux.Image = Ext.extend(Ext.Component, {
	url  : Ext.BLANK_IMAGE_URL  //for initial src value
	,autoEl: {
		tag: 'img'
		,src: Ext.BLANK_IMAGE_URL
		,cls: 'tng-managed-image'
		,width: 'auto'
		,height: 100
	}
//  Add our custom processing to the onRender phase.
//  We add a ‘load’ listener to our element.
	,onRender: function() {
		Ext.ux.Image.superclass.onRender.apply(this, arguments);
		this.el.on('load', this.onLoad, this);
		if(this.url){
			this.setSrc(this.url);
		}
	}
	,onLoad: function() {
		this.fireEvent('load', this);
	}
	,setSrc: function(src) {
		if(src == '' || src == undefined) {
			this.el.dom.src = Ext.BLANK_IMAGE_URL;
			Ext.getCmp('currimg').hide();
		}
		else {
			this.el.dom.src = MODx.config.connectors_url+'system/phpthumb.php?&src='+src+'&wctx=mgr&h=100&zc=0';
			Ext.getCmp('currimg').show();
		}
	}
});
Ext.reg('image', Ext.ux.Image);

Bannerx.grid.Ads = function(config) {
	config = config || {};
	this.exp = new Ext.grid.RowExpander({
		expandOnDblClick: false
		,tpl : new Ext.Template('<p class="desc">{description}</p>')
		,renderer : function(v, p, record){return record.data.description != '' ? '<div class="x-grid3-row-expander">&#160;</div>' : '&#160;';}
	});
	Ext.applyIf(config,{
		id: 'bannerx-grid-ads'
		,url: Bannerx.config.connectorUrl
		,baseParams: { action: 'mgr/ads/getlist' }
		,fields: ['id','name', 'url', 'image', 'active', 'positions', 'clicks', 'description']
		,paging: true
		,border: false
		,frame: false
		,remoteSort: true
		,anchor: '97%'
		,autoExpandColumn: 'name'
		,plugins: this.exp
		,columns: [this.exp
			,{header: _('id'),dataIndex: 'id',sortable: true,width: 10}
			,{header: _('bannerx.ads.name'),dataIndex: 'name',sortable: true}
			,{header: _('bannerx.ads.url'),dataIndex: 'url',sortable: true}
			,{header: _('bannerx.ads.clicks'),dataIndex: 'clicks',sortable: false}
			,{header: _('bannerx.ads.active'),dataIndex: 'active',sortable: true, renderer: this.renderBoolean}
			,{header: _('bannerx.ads.image'),dataIndex: 'image',sortable: false,renderer: {fn:function(img) {return renderGridImage(img,30)}}}
			,{header: _('bannerx.ads.description'),dataIndex: 'description',sortable: false, hidden: true}
		]
		,tbar: [{
			text: _('bannerx.ads.new')
			,handler: this.createAd
		},{
			xtype: 'tbfill'
		},{
			xtype: 'bannerx-filter-positions'
			,id: 'bannerx-grid-ads-positionsfilter'
			,width: 200
			,listeners: {'select': {fn: this.FilterByPosition, scope:this}}
		},{
			xtype: 'tbspacer'
			,width: 10
		}, {
			xtype: 'bannerx-filter-byquery'
			,id: 'bannerx-ads-filter-byquery'
			,listeners: {
				render: {fn: function(tf) {tf.getEl().addKeyListener(Ext.EventObject.ENTER, function() {this.FilterByQuery(tf);}, this);},scope: this}
			}
		},{
			xtype: 'bannerx-filter-clear'
			,listeners: {click: {fn: this.FilterClear, scope: this}}
		}]
		,listeners: {
			rowDblClick: function(grid, rowIndex, e) {
				var row = grid.store.getAt(rowIndex);
				this.updateAd(grid, e, row);
			}
		}
	});

    //positions store/array for checkboxes in add/update window
    Bannerx.positionsArray = new Array();
    Bannerx.posStore = new Ext.data.JsonStore({
       url: Bannerx.config.connectorUrl
      ,root: 'results'
      ,baseParams: { action: 'mgr/positions/getlist' }
      ,fields: ["id", "name"]
      ,autoLoad: true
      ,listeners: {
          load: function(t, records, options) {
				Bannerx.positionsArray = new Array;
				for (var i=0; i<records.length; i++) {
					Bannerx.positionsArray.push({name: "positions[]", inputValue: records[i].data.id, boxLabel: records[i].data.name});
				}
          }
      }
    });

    Bannerx.grid.Ads.superclass.constructor.call(this,config);
};
Ext.extend(Bannerx.grid.Ads,MODx.grid.Grid,{
	getMenu: function(grid,idx) {
		var row = grid.store.data.items[idx]
		var m = new Array;
		if (row.data.active == 0) {
			m.push({text: _('bannerx.ads.enable'),handler: this.enableAd});
		}
		else {
			m.push({text: _('bannerx.ads.disable'),handler: this.disableAd});
		}
		m.push(
			{text: _('bannerx.ads.update'),handler: this.updateAd}
			,'-'
			,{text: _('bannerx.ads.remove'),handler: this.removeAd}
		);
		this.addContextMenuItem(m);
		return true;
	}
	,FilterClear: function() {
		var s = this.getStore();
		s.baseParams.query = '';
		s.baseParams.position = '';
		Ext.getCmp('bannerx-ads-filter-byquery').reset();
		Ext.getCmp('bannerx-grid-ads-positionsfilter').reset();
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
		if (Bannerx.positionsArray.length == 0) {
			MODx.msg.alert(_('error'),_('bannerx.error.no_positions'));
			Ext.getCmp('bannerx-tabs').setActiveTab('bannerx-positions');
			return false;
		}
		w = MODx.load({
			xtype: 'bannerx-window-ad'
			,update: 0
			,openTo: '/'
            ,baseParams: {
                action: 'mgr/ads/create'
            }
			,listeners: {
				'success': {fn:this.refresh,scope:this}
				,'hide': {fn:function() {this.getEl().remove()}}
			}
		});
		w.setTitle(_('bannerx.ads.new')).show(e.target,function() {w.setPosition(null,50)},this);
		Ext.getCmp('bannerx-window-ad').reset();
		Ext.getCmp('currimg').setSrc('');
	}
	,updateAd: function(btn,e, row) {
		if (typeof(row) != 'undefined') {this.menu.record = row.data;}
		if (Bannerx.positionsArray.length == 0) {
			MODx.msg.alert(_('error'),_('bannerx.error.no_positions'));
			Ext.getCmp('bannerx-tabs').setActiveTab('bannerx-positions');
			return false;
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
			url: Bannerx.config.connectorUrl
			,params: {
				action: 'mgr/ads/get'
				,id: this.menu.record.id
			}
			,listeners: {
				'success': {fn:function(r) {
					var record = r.object;

					w = MODx.load({
						xtype: 'bannerx-window-ad'
						,update: 1
						,openTo: openTo
						,listeners: {
							'success': {fn:this.refresh,scope:this}
							,'hide': {fn:function() {this.getEl().remove()}}
						}
					});

					record.newimage = record.image;
					w.setTitle(_('bannerx.ads.update')).show(e.target,function() {w.setPosition(null,50)},this);
					Ext.getCmp('bannerx-window-ad').reset();
					Ext.getCmp('bannerx-window-ad').setValues(record);
					this.enablePositions(record.positions);
					Ext.getCmp('currimg').setSrc(record.image);
				},scope:this}
			}
		});
	}
	,removeAd: function() {
		MODx.msg.confirm({
			title: _('bannerx.ads.remove')
			,text: _('bannerx.ads.remove.confirm')
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
			url: Bannerx.config.connectorUrl
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
			url: Bannerx.config.connectorUrl
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
Ext.reg('bannerx-grid-ads',Bannerx.grid.Ads);

Bannerx.window.Ad = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		id: 'bannerx-window-ad'
		,title: _('bannerx.ads.new')
		,url: Bannerx.config.connectorUrl
		//,fileUpload: true
		,modal: true
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
				,fieldLabel: _('bannerx.ads.name')
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
								xtype: 'textfield'
								,fieldLabel: _('bannerx.ads.url')
								,name: 'url'
								,anchor: '99%'
								,allowBlank: true
							}]
						},{
							columnWidth: .2
							,border: false
							,layout: 'form'
							,items: [{
								xtype: 'xcheckbox'
								,fieldLabel: _('bannerx.ads.active')
								,name: 'active'
								,inputValue: 1
							}]
						}]
					}]
				}]
			},{
				id: 'currimg'
				,fieldLabel: _('bannerx.ads.image.current')
				,xtype: 'image'
			},{
				xtype: 'modx-combo-browser'
				,fieldLabel: config.update ? '' : _('bannerx.ads.image.new')
				,name: 'newimage'
				,source: MODx.config.default_media_source
				,hideFiles: true
				,anchor: '99%'
				,allowBlank: false
				,openTo: config.openTo || '/'
				,listeners: {
					'select': {
						fn:function(data) {
							Ext.getCmp('currimg').setSrc(data.fullRelativeUrl);
							Ext.getCmp('image').setValue(data.fullRelativeUrl);
						}
					}
				}
			},{
				xtype: 'textarea'
				,fieldLabel: _('bannerx.ads.description')
				,name: 'description'
				,anchor: '99%'
				,height: 100
				,allowBlank: true
				,resize: true
			},{
				xtype: 'checkboxgroup'
				,id: 'positions'
				,columns: 3
				,items: Bannerx.positionsArray
				,fieldLabel: _('bannerx.positions')
				,name: 'positions'
			}
		]
		,keys: [{
			key: Ext.EventObject.ENTER
			,shift: true
			,fn:  function() {this.submit()}
			,scope: this
		}]
	});
	Bannerx.window.Ad.superclass.constructor.call(this,config);
};
Ext.extend(Bannerx.window.Ad,MODx.Window);
Ext.reg('bannerx-window-ad',Bannerx.window.Ad);
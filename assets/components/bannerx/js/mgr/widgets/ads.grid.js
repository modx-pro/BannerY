Ext.BLANK_IMAGE_URL = '/assets/components/bannerx/img/_blank.png'

Ext.ux.Image = Ext.extend(Ext.Component, {

    url  : Ext.BLANK_IMAGE_URL,  //for initial src value

    autoEl: {
        tag: 'img',
        src: Ext.BLANK_IMAGE_URL,
        cls: 'tng-managed-image',
        width: 150,
        height: 100
    },
//  Add our custom processing to the onRender phase.
//  We add a ‘load’ listener to our element.
    onRender: function() {
        Ext.ux.Image.superclass.onRender.apply(this, arguments);
        this.el.on('load', this.onLoad, this);
        if(this.url){
            this.setSrc(this.url);
        }
    },
    onLoad: function() {
        this.fireEvent('load', this);
    },
    setSrc: function(src) {
        if(src == '' || src == undefined) {
            this.el.dom.src = Ext.BLANK_IMAGE_URL;
            Ext.getCmp('currimg').hide();
        }
        else {
            this.el.dom.src = MODx.config.connectors_url+'system/phpthumb.php?h=30&src='+src+'&wctx=web&w=150&h=100&zc=0&source=1';
            Ext.getCmp('currimg').show();
        }
    }
});
Ext.reg('image', Ext.ux.Image);

Ext.onReady(function(){
    Bannerx.posStore = new Ext.data.JsonStore({
       url: Bannerx.config.connectorUrl
      ,root: 'results'
      ,baseParams: { action: 'mgr/positions/getlist' }
      ,fields: ["id", "name"]
      ,autoLoad: false
      ,listeners: {
          load: function(t, records, options) {
			  Bannerx.positionsArray = new Array();
              for (var i=0; i<records.length; i++) {
                Bannerx.positionsArray.push({name: "positions[]", inputValue: records[i].data.id, boxLabel: records[i].data.name});
              }
          }
      }
    });
    Bannerx.posStore.load();
});

Bannerx.grid.Ads = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        id: 'bannerx-grid-ads'
        ,url: Bannerx.config.connectorUrl
        ,baseParams: { action: 'mgr/ads/getlist' }
        ,fields: ['id','name', 'url', 'image', 'active', 'positions', 'clicks']
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
            header: _('bannerx.ads.name')
            ,dataIndex: 'name'
            ,sortable: true
        },{
            header: _('bannerx.ads.url')
            ,dataIndex: 'url'
            ,sortable: true
        },{
            header: _('bannerx.ads.clicks')
            ,dataIndex: 'clicks'
            ,sortable: false
        }]
        ,tbar: [{
            text: _('bannerx.ads.new')
            ,handler: this.createAd
        }]
    });
    Bannerx.grid.Ads.superclass.constructor.call(this,config)
};
Ext.extend(Bannerx.grid.Ads,MODx.grid.Grid,{
    getMenu: function() {
        var m = [{
                text: _('bannerx.ads.update')
                ,handler: this.updateAd
            },'-',{
                text: _('bannerx.ads.remove')
                ,handler: this.removeAd
            }];
        this.addContextMenuItem(m);
        return true;
    }
    ,createAd: function(btn,e) {
		if (Bannerx.positionsArray.length == 0) {
			MODx.msg.alert(_('error'),_('bannerx.error.no_positions'));
			Ext.getCmp('bannerx-tabs').setActiveTab('bannerx-positions');
			return false;
		}
		this.AdWindow = MODx.load({
			xtype: 'bannerx-window-ad'
			,update: 0
			,listeners: {
				'success': {fn:this.refresh,scope:this}
				,'hide': {fn:this.destroy}
			}
		});
        this.AdWindow.show(e.target);
        this.AdWindow.setTitle(_('bannerx.ads.new'));
        Ext.getCmp('bannerx-window-ad').reset();
        Ext.getCmp('currimg').setSrc('');
    }
    ,updateAd: function(btn,e) {
		if (Bannerx.positionsArray.length == 0) {
			MODx.msg.alert(_('error'),_('bannerx.error.no_positions'));
			Ext.getCmp('bannerx-tabs').setActiveTab('bannerx-positions');
			return false;
		}
		this.AdWindow = MODx.load({
			xtype: 'bannerx-window-ad'
			,update: 1
			,listeners: {
				'success': {fn:this.refresh,scope:this}
				,'hide': {fn:this.destroy}
			}
		});
		this.menu.record.newimage = this.menu.record.image;
        this.AdWindow.setTitle(_('bannerx.ads.update'));
        this.AdWindow.show(e.target);
        Ext.getCmp('bannerx-window-ad').reset();
        Ext.getCmp('bannerx-window-ad').setValues(this.menu.record);
        this.enablePositions(this.menu.record.positions);
        Ext.getCmp('currimg').setSrc(this.menu.record.image);
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
});
Ext.reg('bannerx-grid-ads',Bannerx.grid.Ads);

Bannerx.window.Ad = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        id: 'bannerx-window-ad'
        ,title: _('bannerx.ads.new')
        ,url: Bannerx.config.connectorUrl
        ,fileUpload: true
        ,baseParams: {
            action: 'mgr/ads/update'
        }
        ,fields: [
            {
                xtype: 'hidden'
                ,name: 'id'
            },{
                xtype: 'hidden'
                ,name: 'image'
                ,id: 'image'
            },{
                xtype: 'textfield'
                ,fieldLabel: _('bannerx.ads.name')
                ,name: 'name'
                ,width: 300
                ,allowBlank: false
            },{
                xtype: 'textfield'
                ,fieldLabel: _('bannerx.ads.url')
                ,name: 'url'
                ,width: 300
                ,allowBlank: false
            },{
                id: 'currimg'
                ,fieldLabel: _('bannerx.ads.image.current')
                ,xtype: 'image'
            },{
                xtype: 'modx-combo-browser'
                ,fieldLabel: config.update ? '' : _('bannerx.ads.image.new')
                ,name: 'newimage'
                ,width: 300
                ,allowBlank: false
                ,listeners: {
                    'select': {
                        fn:function(data) {
                            Ext.getCmp('currimg').setSrc(data.fullRelativeUrl);
                            Ext.getCmp('image').setValue(data.fullRelativeUrl);
                        }
                    }
                }
            },{
                xtype: 'xcheckbox'
                ,name: 'active'
                ,inputValue: 1
                ,fieldLabel: _('bannerx.ads.active')
            },{
                xtype: 'checkboxgroup'
                ,id: 'positions'
                ,items: Bannerx.positionsArray
                ,fieldLabel: _('bannerx.positions')
                ,name: 'positions'
            }
        ]
    });
    Bannerx.window.Ad.superclass.constructor.call(this,config);
};
Ext.extend(Bannerx.window.Ad,MODx.Window);
Ext.reg('bannerx-window-ad',Bannerx.window.Ad);

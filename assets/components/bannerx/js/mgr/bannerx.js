var Bannerx = function(config) {
    config = config || {};
    Bannerx.superclass.constructor.call(this,config);
};
Ext.extend(Bannerx,Ext.Component,{
    page:{},window:{},grid:{},tree:{},panel:{},combo:{},config: {}
});
Ext.reg('bannerx',Bannerx);

Bannerx = new Bannerx();


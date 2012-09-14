var Bannery = function(config) {
    config = config || {};
    Bannery.superclass.constructor.call(this,config);
};
Ext.extend(Bannery,Ext.Component,{
    page:{},window:{},grid:{},tree:{},panel:{},combo:{},config: {}
});
Ext.reg('bannery',Bannery);

Bannery = new Bannery();


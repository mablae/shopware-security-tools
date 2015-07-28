//{namespace name=backend/mittwald_security_tools/main}
//{block name="backend/mittwald_security_tools/controller/main"}
Ext.define('Shopware.apps.MittwaldSecurityTools.controller.Main', {
    extend    : 'Enlight.app.Controller',
    init      : function () {
        var me = this;

        me.checkResultStore = me.getStore('CheckResult');
        me.failedLoginStore = me.getStore('FailedLogin');

        me.mainWindow = me.getView('main.Window').create({
            checkResultStore: me.checkResultStore,
            failedLoginStore: me.failedLoginStore
        }).show();
    }
});
//{/block}
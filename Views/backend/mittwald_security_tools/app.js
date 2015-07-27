/**
 * Definiert eine ExtJS-Applikation und die verwendeten Controller, Stores, Models und Views
 */

//{block name="backend/mittwald_security_tools/application"}
Ext.define('Shopware.apps.MittwaldSecurityTools', {
    name       : 'Shopware.apps.MittwaldSecurityTools',
    extend     : 'Enlight.app.SubApplication',
    bulkLoad   : true,
    loadPath   : '{url action=load}',
    controllers: ['Main'],
    views      : ['main.Window', 'tabs.Main', 'default.Accordion'],
    stores     : ['CheckResult'],
    models     : ['CheckResult'],
    launch     : function () {
        var me = this;
        var controller = me.getController('Main');
        return controller.mainWindow;
    }
});
//{/block}
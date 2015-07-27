//{namespace name="backend/mittwald_security_tools/tabs"}
//{block name="backend/mittwald_security_tools/view/tabs/main"}
Ext.define('Shopware.apps.MittwaldSecurityTools.view.tabs.Main', {
    extend       : 'Ext.tab.Panel',
    alias        : 'widget.mittwald-security-tools-tabs-main',
    initComponent: function () {
        var me = this;

        me.items = [
            Ext.create('Shopware.apps.MittwaldSecurityTools.view.default.Accordion',
                {
                    checkResultStore: me.checkResultStore
                })
        ];

        me.callParent(arguments);
    }
});
//{/block}
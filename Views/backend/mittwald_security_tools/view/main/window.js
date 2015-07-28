//{namespace name="backend/mittwald_security_tools/main"}
//{block name="backend/mittwald_security_tools/view/main/window"}
Ext.define('Shopware.apps.MittwaldSecurityTools.view.main.Window', {
    extend       : 'Enlight.app.Window',
    alias        : 'widget.mittwald-security-tools-main-window',
    layout       : 'fit',
    width        : 860,
    height       : '90%',
    stateful     : true,
    stateId      : 'mittwald-security-tools-main-window',
    title        : '{s name=main/window/title}Mittwald Security Tools{/s}',
    initComponent: function () {
        var me = this;

        me.mainTabPanel = Ext.create(
            'Shopware.apps.MittwaldSecurityTools.view.tabs.Main',
            {
                checkResultStore: me.checkResultStore,
                failedLoginStore: me.failedLoginStore
            }
        );
        me.items = [
            me.mainTabPanel
        ];

        me.callParent(arguments);
    }
});
//{/block}
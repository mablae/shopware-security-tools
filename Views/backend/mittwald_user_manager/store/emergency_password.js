//{block name="backend/user_manager/store/user" append}
Ext.define('Shopware.apps.MittwaldSecurityTools.store.EmergencyPassword', {
    extend: 'Shopware.store.Listing',

    configure: function () {
        return {
            controller: 'MittwaldEmergencyPasswords'
        };
    },
    model    : 'Shopware.apps.MittwaldSecurityTools.model.EmergencyPassword',
    autoLoad : false
});
//{/block}
Ext.define('Shopware.apps.MittwaldSecurityTools.store.FailedLogin', {
    extend: 'Shopware.store.Listing',

    configure: function () {
        return {
            controller: 'MittwaldFailedLogins'
        };
    },
    model    : 'Shopware.apps.MittwaldSecurityTools.model.FailedLogin',
    autoLoad : true
});
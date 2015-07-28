Ext.define('Shopware.apps.MittwaldSecurityTools.model.FailedLogin', {
    extend: 'Shopware.data.Model',

    configure: function() {
        return {
            controller: 'MittwaldFailedLogins'
        };
    },

    fields: [
        { name : 'id', type: 'int', useNull: true },
        { name : 'username', type: 'string' },
        { name : 'ip', type: 'string' },
        { name : 'created', type: 'date' },
        { name : 'isBackend', type: 'boolean' }
    ]
});
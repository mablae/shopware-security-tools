//{block name="backend/user_manager/model/user" append}
Ext.define('Shopware.apps.MittwaldSecurityTools.model.EmergencyPassword', {
    extend: 'Shopware.data.Model',

    configure: function () {
        return {
            controller: 'MittwaldEmergencyPasswords'
        };
    },

    fields: [
        {
            name: 'id',
            type: 'int',
        },
        {
            name: 'password',
            type: 'string'
        },
        {
            name: 'created',
            type: 'date'
        },
        {
            name: 'isUsed',
            type: 'boolean'
        }
    ]
});
//{/block}
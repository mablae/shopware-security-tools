//{block name="backend/mittwald_security_tools/model/check_result"}
Ext.define('Shopware.apps.MittwaldSecurityTools.model.CheckResult', {
    extend: 'Ext.data.Model',

    fields: [
        //{block name="backend/mittwald_security_tools/model/check_result/fields"}{/block}
        {
            name: 'id', type: 'int'
        },
        {
            name: 'reason', type: 'string'
        },
        {
            name: 'value', type: 'string'
        }
    ],


    proxy: {
        type: 'ajax',

        api: {
            read: '{url action="doChecks"}'
        },

        reader: {
            type: 'json',
            root: 'data'
        }
    }

});
//{/block}
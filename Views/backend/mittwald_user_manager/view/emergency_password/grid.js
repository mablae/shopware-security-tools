//{block name="backend/user_manager/view/user/create" prepend}
Ext.define('Shopware.apps.MittwaldSecurityTools.view.emergencyPassword.Grid', {
    extend : 'Ext.grid.Panel',
    title  : 'Notfall Passwörter',
    columns: [
        {
            text     : 'Passwort',
            dataIndex: 'password',
            flex     : 1
        }
    ],
    dockedItems: [{
        xtype: 'toolbar',
        dock: 'top',
        ui: 'shopware-ui',
        items: [{
            xtype: 'button',
            text: 'Neu generieren',
            iconCls: 'sprite-plus-circle-frame',
            handler: function(){
                var me = this;
                var store =  me.up('grid').store;

                Ext.Ajax.request({
                    url: '{url controller="MittwaldEmergencyPasswords" action="create"}',
                    params: {
                        userID: store.proxy.extraParams.userID
                    },
                    success: function(response, opts) {
                        store.reload();
                    }
                });

            }
        }, {
                xtype: 'button',
                text: 'Download CSV',
                iconCls: 'sprite-plus-circle-frame',
                handler: function(){
                    var me = this;
                    var store =  me.up('grid').store;

                    window.open(
                        '{url controller="MittwaldEmergencyPasswords" action="listCSV"}?userID=' + store.proxy.extraParams.userID,
                        '_blank'
                    );

                }
            }]
    }]
});
//{/block}
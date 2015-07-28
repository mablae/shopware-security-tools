Ext.define('Shopware.apps.MittwaldSecurityTools.view.failedlogin.Grid', {
    extend       : 'Ext.grid.Panel',
    title        : 'Fehlgeschlagene Loginversuche',
    columns      : [
        {
            text     : 'Zeitpunkt',
            dataIndex: 'created',
            flex     : 1,
            xtype    : 'datecolumn',
            format   : 'd.m.Y H:i:s'
        },
        {
            text     : 'Benutzername',
            dataIndex: 'username',
            flex     : 1
        },
        {
            text     : 'IP',
            dataIndex: 'ip',
            flex     : 1
        },
        {
            text     : 'Modul',
            dataIndex: 'isBackend',
            flex     : 1,
            renderer : function (val) {
                if (val) {
                    return 'Backend';
                }

                return 'Frontend';
            }
        }
    ]
});
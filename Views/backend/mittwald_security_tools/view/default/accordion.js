//{namespace name="backend/mittwald_security_tools/default"}
//{block name="backend/mittwald_security_tools/view/default/accordion"}
Ext.define('Shopware.apps.MittwaldSecurityTools.view.default.Accordion', {
    extend             : 'Ext.panel.Panel',
    title              : 'Allgemein',
    alias              : 'widget.mittwald-security-tools-default-accordion',
    layout             : 'accordion',
    initComponent      : function () {
        var me = this;

        me.items = [
            me.getChecksPanel(),
            me.getHintsPanel()
        ];

        me.callParent(arguments);
    },
    checkResultRenderer: function (val, meta, record, rowIndex) {
        switch (record.get('reason')) {
            case 'noSSL':
                return 'SSL ist für den Shop "' + record.get('value') + '" nicht aktiviert.';
                break;
            case 'badUsername':
                return 'Der Standard Benutzername "' + record.get('value') + '" ist vorhanden und aktiviert.';
                break;
        }
        return val;
    },
    getChecksPanel     : function () {
        var me = this;

        return Ext.create('Ext.grid.Panel', {
            title  : 'Erkannte Sicherheitsprobleme',
            height : '100%',
            store  : me.checkResultStore,
            columns: [
                {
                    header   : 'Problem',
                    dataIndex: 'reason',
                    flex     : 1,
                    renderer : me.checkResultRenderer
                }
            ]
        });
    },
    getHintsPanel      : function () {
        var me = this;

        return {
            title : 'Allgemeine Sicherheitstipps',
            height: '100%',
            html  : '...'
        };
    }
})
;
//{/block}
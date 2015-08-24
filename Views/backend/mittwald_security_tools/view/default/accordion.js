/**
 *
 * Copyright (C) 2015 Philipp Mahlow, Mittwald CM-Service GmbH & Co.KG
 *
 * This plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program (see LICENSE.txt). If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @author Philipp Mahlow <p.mahlow@mittwald.de>
 *
 */

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
            ],
            dockedItems: [
                Ext.create('Ext.toolbar.Paging',
                    {
                        store      : me.checkResultStore,
                        dock       : 'bottom',
                        displayInfo: true
                    }
                )
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
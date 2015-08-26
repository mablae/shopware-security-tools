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
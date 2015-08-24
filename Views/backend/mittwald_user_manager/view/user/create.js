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

//{extends file="parent:backend/user_manager/view/user/create.js"}

//{namespace name=backend/user_manager/view/main}

//{block name="backend/user_manager/view/user/create" append}
Ext.define('Shopware.apps.MittwaldSecurityTools.view.user.Create', {

    override: 'Shopware.apps.UserManager.view.user.Create',
    getUserTab: function () {
        var me = this;
        var tabPanel = me.callParent(arguments);
        var attributeModel = me.record.getAttributes().getAt(0);

        //create an empty attribute model if necessary

        if (!attributeModel) {
            attributeModel = Ext.create('Shopware.apps.UserManager.model.Attribute');
            attributeModel.set('userID', me.record.get('id'));
            me.record.getAttributes().add(attributeModel);
        }

        //add our new tab...

        tabPanel.add(
            me.getYubikeyTab(attributeModel)
        );

        //... and inject the new yubikey secret to the attribute field if necessary
        me.on({
            'saveUser': function (record, formPanel) {
                if (me.yubikeyField.getValue() && me.yubikeyField.getValue().length > 24) {
                    attributeModel.set('mittwaldYubiKey', me.yubikeyField.getValue().substring(0, 12));
                }
            }
        });

        return tabPanel;
    },
    getYubikeyTab: function (attributeModel) {
        var me = this;
        me.yubikeyField = Ext.create('Ext.form.field.Text', {
            fieldLabel: 'YubiKey Secret'
        });

        return Ext.create('Ext.panel.Panel', {
            title: '2-Faktor Authentifizierung',
            layout: 'accordion',
            items: [
                Ext.create('Ext.form.FormPanel',
                    {
                        title: 'Yubikey verbinden',
                        bodyPadding: 10,
                        defaults: {
                            labelWidth: '155px',
                            labelStyle: 'font-weight: 700; text-align: right;'
                        },
                        items: [
                            Ext.create('Ext.form.Label', {
                                html: me.getYubikeyText(attributeModel)
                            }),
                            me.yubikeyField
                        ]
                }),
                Ext.create('Shopware.apps.MittwaldSecurityTools.view.emergencyPassword.Grid', {
                    store: me.getEmergencyPasswordStore()
                })
            ]
        });
    },
    getEmergencyPasswordStore: function () {
        var me = this;
        me.emergencyPasswordStore = Ext.create('Shopware.apps.MittwaldSecurityTools.store.EmergencyPassword');

        //inject the actual userID in the emergency passwords stores params
        me.emergencyPasswordStore.proxy.extraParams.userID = me.record.get('id');
        me.emergencyPasswordStore.load();

        return me.emergencyPasswordStore;
    },
    getYubikeyText: function(attributeModel){
        var me = this;

        var text = 'Es ist ';

        if(attributeModel.get('mittwaldYubiKey'))
        {
            text += 'bereits ein YubiKey zugeordnet. <br/><br/>';
        }
        else
        {
            text += 'noch kein YubiKey zugeordnet. <br/><br/>';
        }

        text += 'Befolgen Sie folgende Schritte, um einen neuen YubiKey mit dem Benutzerkonto zu verbinden. ' +
            'Verbinden Sie Ihren YubiKey per USB mit Ihrem Computer, ' +
            'klicken Sie in das Eingabefeld, drücken Sie den Taster auf dem ' +
            'YubiKey und speichern Sie das Formular. <br/><br/>' +
            '<b>Bitte beachten Sie, dass der Login im Falle eines Verlusts des zugeordneten YubiKeys nicht mehr ' +
            'ohne weiteres möglich ist.</b> Sie können im Tab "Notfall Passwörter" für diesen Zweck Notfall Passwörter ' +
            'generieren. Wenn Sie einen YubiKey für ein Benutzerkonto benutzen, sollten Sie sich <b>auf jeden Fall Notfall ' +
            'Passwörter generieren</b> und diese über den Button "CSV Download" <b>herunterladen, ausdrucken und ' +
            'an einem sicheren Ort verwahren.</b> <br/><br/>' +
            'Um sich bei Verlust des YubiKeys trotzdem einloggen zu können, benutzen Sie eins der Notfall Passwörter als ' +
            'One-Time-Password. <b>Bitte beachten Sie, dass jedes Notfall Passwort nur einmalig für den Login verwendet werden kann.</b>' +
            '<br/><br/>' +
            '<b>Leeren Sie den Cache vollständig, wenn Sie die Zwei-Faktor-Authentifizierung aktivieren oder deaktivieren.</b>' +
            '<br/><br/><br/>';

        return text;
    }
});
//{/block}
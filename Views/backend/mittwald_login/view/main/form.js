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

//{namespace name=backend/login/view/main}


//{block name="backend/login/view/main/form" append}
Ext.define('Shopware.apps.MittwaldSecurityTools.view.main.Form', {
    override: 'Shopware.apps.Login.view.main.Form',
    initComponent: function(){
        var me = this;
        me.callParent(arguments);

        //{if $MittwaldSecurityToolsUseYubikeyAuth}

            me.otpField = Ext.create('Ext.form.field.Text', {
                inputType: 'password',
                name: 'yubikey',
                allowBlank: true,
                emptyText: '{s name=field/otp}One Time Password{/s}'
            });

            me.items.insert(3,me.otpField);

        //{/if}

    }
});
//{/block}
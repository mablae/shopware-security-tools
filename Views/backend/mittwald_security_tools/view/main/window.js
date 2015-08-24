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

//{namespace name="backend/mittwald_security_tools/main"}
//{block name="backend/mittwald_security_tools/view/main/window"}
Ext.define('Shopware.apps.MittwaldSecurityTools.view.main.Window', {
    extend       : 'Enlight.app.Window',
    alias        : 'widget.mittwald-security-tools-main-window',
    layout       : 'fit',
    width        : 860,
    height       : '90%',
    stateful     : true,
    stateId      : 'mittwald-security-tools-main-window',
    title        : '{s name=main/window/title}Mittwald Security Tools{/s}',
    initComponent: function () {
        var me = this;

        me.mainTabPanel = Ext.create(
            'Shopware.apps.MittwaldSecurityTools.view.tabs.Main',
            {
                checkResultStore: me.checkResultStore,
                failedLoginStore: me.failedLoginStore
            }
        );
        me.items = [
            me.mainTabPanel
        ];

        me.callParent(arguments);
    }
});
//{/block}
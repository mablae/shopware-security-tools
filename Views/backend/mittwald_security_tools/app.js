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

//{block name="backend/mittwald_security_tools/application"}
Ext.define('Shopware.apps.MittwaldSecurityTools', {
    name       : 'Shopware.apps.MittwaldSecurityTools',
    extend     : 'Enlight.app.SubApplication',
    bulkLoad   : true,
    loadPath   : '{url action=load}',
    controllers: ['Main'],
    views      : ['main.Window', 'tabs.Main', 'default.Accordion', 'failedlogin.Grid'],
    stores     : ['CheckResult', 'FailedLogin'],
    models     : ['CheckResult', 'FailedLogin'],
    launch     : function () {
        var me = this;
        var controller = me.getController('Main');
        return controller.mainWindow;
    }
});
//{/block}
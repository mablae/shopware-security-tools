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
            bodyPadding: 10,
            html  : 'Sicherheitslücken können sowohl in der verwendeten Technik der Installation (PHP/MySQL) bestehen, ' +
            'als auch in installierter Software (inkl. zusätzlichen Plugins). Darüber hinaus können auch Zugangsdaten von ' +
            'lokalen Rechnern über bspw. Trojaner und Keylogger ausgelesen werden. Beispielweise SSH und Backendzugänge. ' +
            'Erfahrungsgemäß werden vermehrt Dateien per FTP manipuliert. In den meisten Fällen wird Schadcode eingeschleust.' +
            '<br/><br/>' +
            'Schadcode kann verschiedene Ziele verfolgen. Man muß sich bewusst sein, dass sobald Zugriff für Angreifer besteht, ' +
            'alle Daten ausgelesen und missbräuchlich verwendet werden können. Betroffen können neben Produktdaten ebenso ' +
            'Kundenadressen und Kreditkartendaten sein.' +
            '<br/><br/>' +
            'Der Code kann auf verschieden Wegen in Installationen eingeschleust werden und nicht immer eindeutig als solcher identifiziert werden.<br/>' +
            'Folgender Code ist sehr offensichtlich kodiert:<br/>' +
            '$zkQgpeR="pre". "g_re"."place"; $iiCEibee="nmNqr3MRdtQLD49oPy8a"^"A\x28\x00\x1d\x30\x5f\x3f\x2a\x27\x11\x1a\x3e4m\x5b\x24\x0a\x0b\x17\x04"; ' +
            '$zkQgpeR($iiCEibee, "sKHulDXODQ2qUgcv2n3TIHSx9Clt0nbrxjcZnzoqFf[...]"^"\x16\x3d\x29\x19Df1\x29l8A[...]", "ENlBlrxCeKrpYbKZr");' +
            '<br/><br/>' +
            'Dieser ist jedoch ebenso ausführbar wie menschen-lesbarer Code. Die Kodierung erschwert lediglich das Auffinden.<br/><br/>' +
            '<b>Was tun nach einem Befall?</b><br/>' +
            'Bitte ändern Sie in jedem Fall alle Kennwörter des Accounts und prüfen Sie alle lokalen Rechner, die per FTP/SSH ' +
            'auf den Account zugegriffen haben, auf Viren und Trojaner und bereinigen Sie diese.<br/>' +
            'Im Anschluss muss jede Datei auf Schadcode geprüft werden um diesen zu entfernen. Hierzu ist der FTP/SSH ' +
            'Zugriff mit neuem Passwort zu verwenden. <br/>' +
            'Grundsätzlich ist es wichtig, die installierten Anwendungen (System und deren Plugins etc.) auf einem aktuellen Stand zu halten.<br/><br/>' +
            'Absolute Sicherheit kann es nicht geben, aber wenn Sie unsere Hinweise beachten, sichere Passwörter verwenden, ' +
            'diese regelmäßig ändern, nicht unbedacht herausgeben und Ihr System (lokal wie auch auf dem Server) auf einem ' +
            'aktuellen Stand halten, haben Sie die größten Sicherheitsrisiken schon eliminiert. Weiterhin sollten Sie allen ' +
            'Benutzerkonten und Zugängen nur die Rechte einräumen, die sie unbedingt brauchen, um einem potentiellen Angreifer ' +
            'möglichst wenig Angriffsfläche zu bieten. Wenn Sie die Zwei-Faktor-Authentifizierung für alle Backend-Zugänge aktivieren, ' +
            'sorgen Sie für etwas mehr Sicherheit, da alleine die Kenntnis von Benutzername und Passwort nicht mehr ausreicht um ein ' +
            'Benutzerkonto zu übernehmen.'
        };
    }
})
;
//{/block}
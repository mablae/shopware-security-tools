<?php

/**
 * Class Shopware_Plugins_Frontend_MittwaldSecurityTools_Bootstrap
 *
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
 */
class Shopware_Plugins_Core_MittwaldSecurityTools_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{

    /**
     * @var bool
     */
    protected $isInitialized = FALSE;


    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Mittwald Security Tools';
    }


    /**
     * @return string
     */
    public function getVersion()
    {
        return "1.3.0";
    }


    /**
     * @return array
     */
    public function getInfo()
    {
        return array(
            'version' => $this->getVersion(),
            'copyright' => 'Copyright (c) 2016, Philipp Mahlow, Mittwald CM-Service GmbH & Co.KG',
            'label' => $this->getLabel(),
            'description' => file_get_contents($this->Path() . 'info.txt'),
            'link' => 'http://www.mittwald.de',
            'author' => 'Philipp Mahlow | Mittwald CM-Service GmbH & Co.KG',
            'license' => 'GPL v3'
        );
    }


    /**
     * install our plugin and call further init methods
     *
     * @return array|bool
     */
    public function install()
    {
        try {
            $this->registerEvents();
            $this->registerControllers();
            $this->createMenuEntries();
            $this->createCronJobs();
            $this->createForm();
            $this->createSchema();
            $this->insertMailTemplate();

            return TRUE;
        } catch (Exception $ex) {
            return [
                'success' => FALSE,
                'message' => $ex->getMessage()
            ];
        }
    }

    /**
     * the update routine
     *
     * @param string $version
     * @return bool
     */
    public function update($version)
    {
        try {
            if ($version == '1.0.0') {
                //rename attribute field
                Shopware()->Db()->exec('ALTER TABLE `s_core_auth_attributes` CHANGE `Mittwald_Yubikey` `mittwald_yubikey` VARCHAR(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;');

                /**
                 * @var \Shopware\Bundle\AttributeBundle\Service\CrudService $service
                 */
                $service = $this->get('shopware_attribute.crud_service');
                $service->update('s_core_auth_attributes', 'Mittwald_YubiKey', 'string', [], null, true);
                $service->update('s_user_attributes', 'mittwald_lastlockedaccountmail', 'datetime', [], null, true);
                $this->createForm();
                $this->insertLockedAccountMailTemplate();
            } else if ($version == '1.1.1' || $version == '1.2.0' || $version == '1.2.1') {
                $this->createForm();
                $this->insertLockedAccountMailTemplate();
                /**
                 * @var \Shopware\Bundle\AttributeBundle\Service\CrudService $service
                 */
                $service = $this->get('shopware_attribute.crud_service');
                $service->update('s_user_attributes', 'mittwald_lastlockedaccountmail', 'datetime', [], null, true);
            }
            return TRUE;
        } catch (Exception $ex) {
            return FALSE;
        }
    }

    /**
     * Enable plugin method
     *
     * @return bool
     */
    public function enable()
    {
        return array(
            'success' => TRUE,
            'invalidateCache' => array(
                'backend',
                'theme',
                'template'
            )
        );
    }

    /**
     * Disable plugin method
     *
     * @return bool
     */
    public function disable()
    {
        return array(
            'success' => TRUE,
            'invalidateCache' => array(
                'backend',
                'theme',
                'template'
            )
        );
    }


    /**
     * remove db-stuff and mail templates on uninstall.
     *
     * @return bool
     */
    public function uninstall()
    {
        $this->removeMailTemplate();
        $this->dropSchema();

        return TRUE;
    }


    /**
     * register our models
     */
    public function afterInit()
    {
        $this->registerCustomModels();
    }


    /**
     * Listener for Enlight_Controller_Front_StartDispatch
     * Registers our namespace and adds our SecuritySubscriber
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onStartDispatch(Enlight_Event_EventArgs $args)
    {
        /*
         * prevent double initialization
         */
        if (!$this->isInitialized) {
            $this->Application()->Loader()->registerNamespace(
                'Shopware\\Mittwald\\SecurityTools',
                $this->Path()
            );

            $subscriber = new \Shopware\Mittwald\SecurityTools\Subscribers\SecuritySubscriber(
                $this->Config(),
                Shopware()->Config(),
                $this->get('models'),
                $this->get('db'),
                $this->get('templatemail'),
                $this->get('guzzle_http_client_factory'),
                $this->get('snippets'),
                $this->Path(),
                $this->Application()->AppPath(),
                Shopware()->DocPath()
            );

            $this->Application()->Events()->addSubscriber($subscriber);

            $this->isInitialized = TRUE;
        }
    }


    /**
     * add our custom tables to database
     *
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    protected function createSchema()
    {
        $this->registerCustomModels();

        $em = $this->Application()->Models();
        $tool = new \Doctrine\ORM\Tools\SchemaTool($em);

        $classes = array(
            $em->getClassMetadata('Shopware\CustomModels\MittwaldSecurityTools\FailedLogin'),
            $em->getClassMetadata('Shopware\CustomModels\MittwaldSecurityTools\EmergencyPassword')
        );

        try {
            $tool->dropSchema($classes);
        } catch (Exception $e) {
            //ignore
        }
        $tool->createSchema($classes);


        /**
         * @var \Shopware\Bundle\AttributeBundle\Service\CrudService $service
         */
        $service = $this->get('shopware_attribute.crud_service');
        $service->update('s_core_auth_attributes', 'Mittwald_YubiKey', 'string', [], null, true);
        $service->update('s_user_attributes', 'mittwald_lastlockedaccountmail', 'datetime', [], null, true);
    }


    /**
     * drops our database tables
     *
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    protected function dropSchema()
    {
        $this->registerCustomModels();

        $em = $this->Application()->Models();
        $tool = new \Doctrine\ORM\Tools\SchemaTool($em);

        $classes = array(
            $em->getClassMetadata('Shopware\CustomModels\MittwaldSecurityTools\FailedLogin'),
            $em->getClassMetadata('Shopware\CustomModels\MittwaldSecurityTools\EmergencyPassword')
        );

        try {
            $tool->dropSchema($classes);

            /**
             * @var \Shopware\Bundle\AttributeBundle\Service\CrudService $service
             */
            $service = $this->get('shopware_attribute.crud_service');
            $service->delete('s_core_auth_attributes', 'Mittwald_YubiKey', true);
            $service->delete('s_user_attributes', 'mittwald_lastlockedaccountmail');

        } catch (Exception $e) {
            //ignore
        }
    }


    /**
     * creates the config form
     */
    protected function createForm()
    {
        $form = $this->Form();


        $form->setElement('button', 'yubikeyGroup', array(
            'label' => '2-Faktor Authentifizierung',
            'handler' => "function(btn) {}",
            'position' => 10
        ));

        $form->setElement('checkbox', 'useYubicoAuth', array(
            'label' => '2-Faktor Authentifizierung über Yubico aktivieren',
            'required' => TRUE,
            'position' => 20
        ));

        $form->setElement('button', 'failedLoginsGroup', array(
            'label' => 'Fehlgeschlagene Loginversuche',
            'handler' => "function(btn) {}",
            'position' => 30
        ));


        $form->setElement('checkbox', 'logFailedBELogins', array(
            'label' => 'Fehlgeschlagene Backend-Logins loggen',
            'required' => TRUE,
            'position' => 40
        ));

        $form->setElement('checkbox', 'logFailedFELogins', array(
            'label' => 'Fehlgeschlagene Frontend-Logins loggen',
            'required' => TRUE,
            'position' => 50
        ));

        $form->setElement('checkbox', 'cleanUpLogFailedBELogins', array(
            'label' => 'Fehlgeschlagene Backend-Logins bereinigen',
            'required' => TRUE,
            'position' => 60
        ));

        $form->setElement('number', 'cleanUpLogFailedBELoginsInterval', array(
            'label' => 'Vorhaltezeit für fehlgeschlagene Backend-Logins in Tagen',
            'required' => TRUE,
            'value' => 7,
            'position' => 70
        ));

        $form->setElement('checkbox', 'cleanUpLogFailedFELogins', array(
            'label' => 'Fehlgeschlagene Frontend-Logins bereinigen',
            'required' => TRUE,
            'position' => 80
        ));

        $form->setElement('number', 'cleanUpLogFailedFELoginsInterval', array(
            'label' => 'Vorhaltezeit für fehlgeschlagene Frontend-Logins in Tagen',
            'required' => TRUE,
            'value' => 2,
            'position' => 90
        ));

        $form->setElement('checkbox', 'mailNotificationForFailedFELogins', array(
            'label' => 'Mail-Notifications für fehlgeschlagene Frontend-Logins aktivieren',
            'required' => TRUE,
            'position' => 100
        ));

        $form->setElement('number', 'mailNotificationForFailedFELoginsLimit', array(
            'label' => 'Schwellwert für Mail-Notifications (Frontend)',
            'description' => 'Übersteigt die Zahl der fehlgeschlagenen Frontend-Logins innerhalb einer Stunde diesen Schwellwert, wird eine Mail-Notifications verschickt.',
            'required' => TRUE,
            'value' => 10,
            'position' => 110
        ));

        $form->setElement('checkbox', 'mailNotificationForFailedBELogins', array(
            'label' => 'Mail-Notifications für fehlgeschlagene Backend-Logins aktivieren',
            'required' => TRUE,
            'position' => 120
        ));

        $form->setElement('number', 'mailNotificationForFailedBELoginsLimit', array(
            'label' => 'Schwellwert für Mail-Notifications (Backend)',
            'description' => 'Übersteigt die Zahl der fehlgeschlagenen Backend-Logins innerhalb einer Stunde diesen Schwellwert, wird eine Mail-Notifications verschickt.',
            'required' => TRUE,
            'value' => 10,
            'position' => 130
        ));

        $form->setElement('checkbox', 'sendLockedAccountMail', array(
            'label' => 'Mail an Kunden verschicken, wenn Account wegen fehlgeschlagener Loginversuche gesperrt wurde',
            'required' => TRUE,
            'position' => 140
        ));

        $form->setElement('number', 'sendLockedAccountMailInterval', array(
            'label' => 'Zeit zwischen Mails an Kunden bezüglich Accountsperrung (Minuten)',
            'description' => 'Bei 0 wird bei jedem fehlgeschlagenem Loginversuch, nach dem der Account gesperrt ist, jeweils eine Mail verschickt. Bei Zahlen > 0 werden die Mails höchstens im Abstand der definierten Minutenanzahl verschickt.',
            'required' => TRUE,
            'value' => 10,
            'position' => 150
        ));

        $form->setElement('button', 'passwordStrengthGroup', array(
            'label' => 'Passwortstärke',
            'handler' => "function(btn) {}",
            'position' => 160
        ));

        $form->setElement('checkbox', 'showPasswordStrengthForUserRegistration', array(
            'label' => 'Passwort-Stärke in Registrierungsformular anzeigen',
            'required' => TRUE,
            'position' => 170
        ));

        $form->setElement('select', 'minimumPasswordStrength', array(
            'label' => 'Minimal-Anforderungen für Passwort-Stärke im Registrierungsprozess',
            'description' => 'Bei Unterschreitung ist eine Registrierung nicht möglich.',
            'required' => TRUE,
            'value' => 0,
            'store' => array(
                array(0, 'Passwort nicht überprüfen / Shopware Standardverhalten'),
                array(60, 'Geringe Komplexität (Zwei Balken, z.B. Klein- und Großbuchstaben)'),
                array(86, 'Mittlere Komplexität (Drei Balken, z.B. Klein-, Großbuchstaben und Zahlen)'),
                array(100, 'Hohe Komplexität (Vier Balken, Klein- und Großbuchstaben, Zahlen und Sonderzeichen)')
            ),
            'position' => 180
        ));

        $form->setElement('checkbox', 'showRecaptchaForUserRegistration', array(
            'label' => 'reCAPTCHA in Registrierungsformular anzeigen',
            'required' => TRUE,
            'position' => 190
        ));


        $form->setElement('button', 'recaptchaGroup', array(
            'label' => 'reCaptcha',
            'handler' => "function(btn) {}",
            'position' => 200
        ));

        $form->setElement('textfield', 'recaptchaAPIKey', array(
            'label' => 'reCAPTCHA: Websiteschlüssel',
            'required' => TRUE,
            'description' => 'Hier können Sie sich die entsprechenden Schlüssel generieren: https://www.google.com/recaptcha/admin',
            'position' => 210
        ));

        $form->setElement('textfield', 'recaptchaSecretKey', array(
            'label' => 'reCAPTCHA: Geheimer Schlüssel',
            'required' => TRUE,
            'description' => 'Hier können Sie sich die entsprechenden Schlüssel generieren: https://www.google.com/recaptcha/admin',
            'position' => 220
        ));

        $form->setElement('textfield', 'recaptchaLanguageKey', array(
            'label' => 'reCAPTCHA: Sprachcode',
            'required' => FALSE,
            'description' => 'Standardmäßig wird Google die Sprache aus dem Browser auslesen. Wenn Sie eine Sprache vorgeben möchten, können Sie hier einen Sprachcode angeben (siehe https://developers.google.com/recaptcha/docs/language)',
            'scope' => Shopware\Models\Config\Element::SCOPE_SHOP,
            'position' => 230
        ));

        $form->setElement('button', 'variousGroup', array(
            'label' => 'Verschiedenes',
            'handler' => "function(btn) {}",
            'position' => 240
        ));

        $form->setElement('checkbox', 'mailNotificationForModifiedCoreFiles', array(
            'label' => 'Mail-Notifications für veränderte Core-Dateien aktivieren',
            'required' => TRUE,
            'position' => 250
        ));

        $form->setElement('checkbox', 'debugMode', array(
            'label' => 'Debug-Modus aktivieren',
            'required' => TRUE,
            'position' => 260
        ));

    }

    /**
     * register our events
     *
     * there will only be the onStartDispatch handler, which adds our event subscriber
     */
    protected function registerEvents()
    {
        //normal call
        $this->subscribeEvent(
            'Enlight_Controller_Front_StartDispatch',
            'onStartDispatch'
        );

        /*
         * CLI-call will not trigger Enlight_Controller_Front_StartDispatch Event.
         * Use Enlight_Bootstrap_InitResource_Cron Event.
         */
        $this->subscribeEvent(
            'Enlight_Bootstrap_InitResource_Cron',
            'onStartDispatch'
        );
    }

    /**
     * registers our controllers
     *
     * @throws Exception
     */
    protected function registerControllers()
    {
        $this->registerController('Backend', 'MittwaldSecurityTools');
        $this->registerController('Backend', 'MittwaldFailedLogins');
        $this->registerController('Backend', 'MittwaldEmergencyPasswords');
    }

    /**
     * create the menu item
     */
    protected function createMenuEntries()
    {
        $this->createMenuItem(array(
            'label' => 'Mittwald Security Tools',
            'controller' => 'MittwaldSecurityTools',
            'class' => 'mittwald-custom-icon',
            'action' => 'Index',
            'active' => 1,
            'parent' => $this->Menu()->findOneBy(array('label' => 'Einstellungen'))
        ));
    }

    /**
     * add our cronjobs
     */
    protected function createCronJobs()
    {
        $this->createCronJob('Mail Notifications für modifizierte Core-Dateien', 'MittwaldSecurityCheckModifiedCoreFiles');
        $this->createCronJob('Failed Login Mail Notifications', 'MittwaldSecurityCheckFailedLoginNotification', 3600);
        $this->createCronJob('Failed Login Log aufräumen', 'MittwaldSecurityCheckCleanUpFailedLogins');
    }

    /**
     * inserts our mail template
     */
    public function insertMailTemplate()
    {
        $sql = <<<EOT
                    INSERT INTO `s_core_config_mails`
                        (`id`, `stateId`, `name`, `frommail`, `fromname`, `subject`, `content`, `contentHTML`,
                         `ishtml`, `attachment`, `mailtype`, `context`, `dirty`)
                    VALUES (NULL, NULL, 'sFAILEDLOGIN', '{config name=mail}', '{config name=shopName}',
                            'Fehlgeschlagene Loginversuche im {config name=shopName}',
                            '{include file="string:{config name=emailheaderplain}"} \nHallo, \nder Schwellwert für fehlgeschlagene Login-Versuche wurde überschritten. Dies kann möglicherweise auf einen Angriff hinweisen. \n{include file="string:{config name=emailfooterplain}"}',
                            '', '0', '', '2', '', '0');'
EOT;

        $this->get('db')->executeUpdate($sql);


        $sql = <<<EOT
                    INSERT INTO `s_core_config_mails`
                        (`id`, `stateId`, `name`, `frommail`, `fromname`, `subject`, `content`, `contentHTML`,
                         `ishtml`, `attachment`, `mailtype`, `context`, `dirty`)
                    VALUES (NULL, NULL, 'sMODIFIEDFILES', '{config name=mail}', '{config name=shopName}',
                            'Modifizierte Core-Dateien im {config name=shopName}',
                            '{include file="string:{config name=emailheaderplain}"} \nHallo, \nes wurde eine Modifikation an den überwachten Core-Dateien festgestellt. Dies kann möglicherweise auf einen Angriff hinweisen. Bitte prüfen Sie den Status im Shop-Backend unter "Einstellungen"->"Systeminfo"->"Shopware-Dateien". \n{include file="string:{config name=emailfooterplain}"}',
                            '', '0', '', '2', '', '0');'
EOT;

        $this->get('db')->executeUpdate($sql);

        $this->insertLockedAccountMailTemplate();
    }

    /**
     * insert mail template for locked account mail
     */
    public function insertLockedAccountMailTemplate()
    {
        $sql = <<<EOT
                    INSERT INTO `s_core_config_mails`
                        (`id`, `stateId`, `name`, `frommail`, `fromname`, `subject`, `content`, `contentHTML`,
                         `ishtml`, `attachment`, `mailtype`, `context`, `dirty`)
                    VALUES (NULL, NULL, 'sLOCKEDACCOUNT', '{config name=mail}', '{config name=shopName}',
                            'Ihr Konto im {config name=shopName} wurde vorübergehend gesperrt',
                            '{include file="string:{config name=emailheaderplain}"} \nHallo, \nIhr Benutzerkonto im {config name=shopName} wurde wegen zu vieler fehlgeschlagener Loginversuche vorübergehend gesperrt. Wenn Sie sich Ihr Passwort vergessen haben, verwenden Sie bitte die "Passwort vergessen"-Funktion. \n{include file="string:{config name=emailfooterplain}"}',
                            '', '0', '', '2', '', '0');'
EOT;

        $this->get('db')->executeUpdate($sql);
    }

    /**
     * removes our mail template on plugin deinstallation
     */
    public function removeMailTemplate()
    {
        $sql = 'DELETE FROM `s_core_config_mails`
                WHERE name IN ("sFAILEDLOGIN", "sMODIFIEDFILES", "sLOCKEDACCOUNT")';

        $this->get('db')->executeUpdate($sql);
    }

}
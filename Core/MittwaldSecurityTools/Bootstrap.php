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
        return "1.0.3";
    }


    /**
     * @return array
     */
    public function getInfo()
    {
        return array(
            'version' => $this->getVersion(),
            'copyright' => 'Copyright (c) 2015, Philipp Mahlow, Mittwald CM-Service GmbH & Co.KG',
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
     * @param string $version
     * @return bool
     */
    public function update($version)
    {
        return true;
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

        $this->Application()->Models()->addAttribute(
            's_core_auth_attributes',
            'Mittwald',
            'YubiKey',
            'varchar(255)',
            TRUE,
            NULL
        );

        $this->Application()->Models()->generateAttributeModels(array(
            's_core_auth_attributes'
        ));
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

            $this->Application()->Models()->removeAttribute(
                's_core_auth_attributes',
                'Mittwald',
                'YubiKey'
            );

            $this->Application()->Models()->generateAttributeModels(array(
                's_core_auth_attributes'
            ));

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

        $form->setElement('checkbox', 'useYubicoAuth', array(
            'label' => '2-Faktor Authentifizierung über Yubico aktivieren',
            'required' => TRUE
        ));

        $form->setElement('checkbox', 'logFailedBELogins', array(
            'label' => 'Fehlgeschlagene Backend-Logins loggen',
            'required' => TRUE
        ));

        $form->setElement('checkbox', 'logFailedFELogins', array(
            'label' => 'Fehlgeschlagene Frontend-Logins loggen',
            'required' => TRUE
        ));

        $form->setElement('checkbox', 'cleanUpLogFailedBELogins', array(
            'label' => 'Fehlgeschlagene Backend-Logins bereinigen',
            'required' => TRUE
        ));

        $form->setElement('number', 'cleanUpLogFailedBELoginsInterval', array(
            'label' => 'Vorhaltezeit für fehlgeschlagene Backend-Logins in Tagen',
            'required' => TRUE,
            'value' => 7
        ));

        $form->setElement('checkbox', 'cleanUpLogFailedFELogins', array(
            'label' => 'Fehlgeschlagene Frontend-Logins bereinigen',
            'required' => TRUE
        ));

        $form->setElement('number', 'cleanUpLogFailedFELoginsInterval', array(
            'label' => 'Vorhaltezeit für fehlgeschlagene Frontend-Logins in Tagen',
            'required' => TRUE,
            'value' => 2
        ));

        $form->setElement('checkbox', 'mailNotificationForFailedFELogins', array(
            'label' => 'Mail-Notifications für fehlgeschlagene Frontend-Logins aktivieren',
            'required' => TRUE
        ));

        $form->setElement('number', 'mailNotificationForFailedFELoginsLimit', array(
            'label' => 'Schwellwert für Mail-Notifications (Frontend)',
            'description' => 'Übersteigt die Zahl der fehlgeschlagenen Frontend-Logins innerhalb einer Stunde diesen Schwellwert, wird eine Mail-Notifications verschickt.',
            'required' => TRUE,
            'value' => 10
        ));

        $form->setElement('checkbox', 'mailNotificationForFailedBELogins', array(
            'label' => 'Mail-Notifications für fehlgeschlagene Backend-Logins aktivieren',
            'required' => TRUE
        ));

        $form->setElement('number', 'mailNotificationForFailedBELoginsLimit', array(
            'label' => 'Schwellwert für Mail-Notifications (Backend)',
            'description' => 'Übersteigt die Zahl der fehlgeschlagenen Backend-Logins innerhalb einer Stunde diesen Schwellwert, wird eine Mail-Notifications verschickt.',
            'required' => TRUE,
            'value' => 10
        ));

        $form->setElement('checkbox', 'showPasswordStrengthForUserRegistration', array(
            'label' => 'Passwort-Stärke in Registrierungsformular anzeigen',
            'required' => TRUE
        ));

        $form->setElement('checkbox', 'showRecaptchaForUserRegistration', array(
            'label' => 'reCAPTCHA in Registrierungsformular anzeigen',
            'required' => TRUE
        ));

        $form->setElement('textfield', 'recaptchaAPIKey', array(
            'label' => 'reCAPTCHA: Websiteschlüssel',
            'required' => TRUE,
            'description' => 'Hier können Sie sich die entsprechenden Schlüssel generieren: https://www.google.com/recaptcha/admin'
        ));

        $form->setElement('textfield', 'recaptchaSecretKey', array(
            'label' => 'reCAPTCHA: Geheimer Schlüssel',
            'required' => TRUE,
            'description' => 'Hier können Sie sich die entsprechenden Schlüssel generieren: https://www.google.com/recaptcha/admin'
        ));

        $form->setElement('checkbox', 'mailNotificationForModifiedCoreFiles', array(
            'label' => 'Mail-Notifications für veränderte Core-Dateien aktivieren',
            'required' => TRUE
        ));

        $form->setElement('checkbox', 'debugMode', array(
            'label' => 'Debug-Modus aktivieren',
            'required' => TRUE
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
            'parent' => $this->Menu()->findOneBy('label', 'Einstellungen')
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
    }

    /**
     * removes our mail template on plugin deinstallation
     */
    public function removeMailTemplate()
    {
        $sql = 'DELETE FROM `s_core_config_mails`
                WHERE name IN ("sFAILEDLOGIN", "sMODIFIEDFILES")';

        $this->get('db')->executeUpdate($sql);
    }

}
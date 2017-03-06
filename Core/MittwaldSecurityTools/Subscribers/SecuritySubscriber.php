<?php

namespace Shopware\Mittwald\SecurityTools\Subscribers;


use Doctrine\Common\Collections\ArrayCollection;
use Enlight\Event\SubscriberInterface;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\HttpClient\GuzzleFactory;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Theme\LessDefinition;
use Shopware\CustomModels\MittwaldSecurityTools\FailedLogin;
use Shopware\Mittwald\SecurityTools\Components\MittwaldAuth;
use Shopware\Mittwald\SecurityTools\Services\LogService;
use Shopware\Mittwald\SecurityTools\Services\PasswordStrengthService;


/**
 * Class SecuritySubscriber
 * @package Shopware\Mittwald\SecurityTools
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
 *
 */
class SecuritySubscriber implements SubscriberInterface
{


    /**
     * @var \Enlight_Config
     */
    protected $pluginConfig;

    /**
     * @var \Shopware_Components_Config
     */
    protected $shopConfig;


    /**
     * @var ModelManager
     */
    protected $modelManager;


    /**
     * @var LogService
     */
    protected $logger;

    /**
     * @var PasswordStrengthService
     */
    protected $passwordStrengthService;


    /**
     * @var \Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    protected $db;

    /**
     * @var \Shopware_Components_TemplateMail
     */
    protected $templateMail;

    /**
     * @var string
     */
    protected $pluginPath;

    /**
     * @var string
     */
    protected $appPath;

    /**
     * @var string
     */
    protected $docPath;

    /**
     * @var \GuzzleHttp\ClientInterface
     */
    protected $client;

    /**
     * @var \Shopware_Components_Snippet_Manager
     */
    protected $snippets;

    /**
     * @var bool
     */
    protected $captchaChecked = FALSE;


    /**
     * construct the subscriber with all dependencies
     *
     * @param \Enlight_Config $pluginConfig
     * @param \Shopware_Components_Config $shopConfig
     * @param ModelManager $modelManager
     * @param \Enlight_Components_Db_Adapter_Pdo_Mysql $db
     * @param \Shopware_Components_TemplateMail $templateMail
     * @param GuzzleFactory $guzzleFactory
     * @param \Shopware_Components_Snippet_Manager $snippets
     * @param string $pluginPath
     * @param string $appPath
     * @param string $docPath
     */
    public function __construct(\Enlight_Config $pluginConfig,
                                \Shopware_Components_Config $shopConfig,
                                ModelManager $modelManager,
                                \Enlight_Components_Db_Adapter_Pdo_Mysql $db,
                                \Shopware_Components_TemplateMail $templateMail,
                                GuzzleFactory $guzzleFactory,
                                \Shopware_Components_Snippet_Manager $snippets,
                                $pluginPath, $appPath, $docPath)
    {
        $this->pluginConfig = $pluginConfig;
        $this->shopConfig = $shopConfig;
        $this->logger = new LogService($this->pluginConfig);
        $this->passwordStrengthService = new PasswordStrengthService();
        $this->modelManager = $modelManager;
        $this->templateMail = $templateMail;
        $this->db = $db;
        $this->client = $guzzleFactory->createClient();
        $this->snippets = $snippets;
        $this->pluginPath = $pluginPath;
        $this->appPath = $appPath;
        $this->docPath = $docPath;
    }


    /**
     * subscribe our events
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Modules_Admin_Login_FilterResult' => 'logFailedFELogin',
            'Enlight_Controller_Action_PostDispatch_Backend_Login' => 'logFailedBELogin',
            'Shopware_CronJob_MittwaldSecurityCheckModifiedCoreFiles' => 'onCoreFilesCheck',
            'Shopware_CronJob_MittwaldSecurityCheckCleanUpFailedLogins' => 'onLogCleanupCron',
            'Shopware_CronJob_MittwaldSecurityCheckFailedLoginNotification' => 'onCheckNotification',
            'Enlight_Controller_Action_PostDispatchSecure_Backend' => 'addMenuTemplates',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_UserManager' => 'addUserManagerTemplates',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Login' => 'addLoginTemplates',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Register' => 'addTemplates',
            'Enlight_Controller_Action_PostDispatch_Frontend_Register' => 'enhanceAjaxPasswordValidation',
            'Enlight_Controller_Action_Frontend_Register_saveRegister' => 'onSaveRegister',
            'Theme_Compiler_Collect_Plugin_Less' => 'onCollectLessFiles',
            'Theme_Compiler_Collect_Plugin_Javascript' => 'onCollectJSFiles',
            'Enlight_Bootstrap_AfterInitResource_Auth' => ['onAfterInitAuth', 999999]
        ];
    }


    /**
     * decorates the default auth component
     *
     * @param \Enlight_Event_EventArgs $args
     * @throws \Exception
     */
    public function onAfterInitAuth(\Enlight_Event_EventArgs $args)
    {
        if (!$this->pluginConfig->useYubicoAuth) {
            return;
        }

        /**
         * @var Container $subject
         */
        $subject = $args->getSubject();


        /**
         * @var \Shopware_Components_Auth $originalAuth
         */
        $originalAuth = $subject->get('auth');
        $auth = MittwaldAuth::getInstance();
        $auth->init($originalAuth, $this->db, $this->client, $this->logger);

        $subject->set(
            'auth',
            $auth
        );

        return;
    }

    /**
     * add the less sources for our password strength template
     *
     * @return ArrayCollection
     */
    public function onCollectLessFiles()
    {
        $lessDir = $this->pluginPath . '/Views/frontend/_public/src/less/';

        $less = new LessDefinition(
            array(),
            array(
                $lessDir . 'all.less'
            )
        );

        return new ArrayCollection(array($less));
    }

    /**
     * add the script for our password strength template
     *
     * @return ArrayCollection
     */
    public function onCollectJSFiles()
    {
        $jsDir = $this->pluginPath . '/Views/frontend/_public/src/js/';

        return new ArrayCollection(array(
            $jsDir . 'jQuery.passwordStrength.js'
        ));
    }


    /**
     * triggers the shopware file check and sends an email-notification, if any modification was detected
     *
     * @param \Enlight_Event_EventArgs $args
     * @return bool
     * @throws \Enlight_Exception
     */
    public function onCoreFilesCheck(\Enlight_Event_EventArgs $args)
    {
        if (!$this->pluginConfig->mailNotificationForModifiedCoreFiles) {
            return TRUE;
        }

        $fileName = $this->appPath . '/Components/Check/Data/Files.md5sums';


        if (!is_file($fileName)) {
            $this->logger->error('CORE-FILES-CHECK', 'checksum file could not be loaded');
            return FALSE;
        }

        $list = new \Shopware_Components_Check_File($fileName, $this->docPath, []);

        foreach ($list->toArray() as $file) {
            if (!$file['result']) {
                $mail = $this->templateMail->createMail('sMODIFIEDFILES');
                $mail->addTo($this->shopConfig->get('sMAIL'));
                $mail->send();

                return TRUE;
            }
        }

        return TRUE;
    }

    /**
     * replacement for save register. will check the google reCAPTCHA and pipe data to original action, if captcha is valid
     * or captcha validation is not activated.
     *
     * @param \Enlight_Event_EventArgs $args
     * @return bool|null
     */
    public function onSaveRegister(\Enlight_Event_EventArgs $args)
    {
        /**
         * @var \Shopware_Controllers_Frontend_Register $controller
         */
        $controller = $args->getSubject();

        $postData = $controller->Request()->getPost();

        $errors = array(
            'personal' => array()
        );


        if ($this->pluginConfig->showRecaptchaForUserRegistration && !$this->captchaChecked) {
            $gCaptchaResponse = isset($postData['g-recaptcha-response']) ? $postData['g-recaptcha-response'] : FALSE;

            $response = $this->client->post('https://www.google.com/recaptcha/api/siteverify', [
                'body' => [
                    'secret' => $this->pluginConfig->recaptchaSecretKey,
                    'response' => $gCaptchaResponse
                ]
            ]);

            $responseData = json_decode($response->getBody(), TRUE);

            if (!$responseData['success']) {
                if (is_array($responseData['error-codes']) &&
                    (in_array('missing-input-secret', $responseData['error-codes']) ||
                        in_array('invalid-input-secret', $responseData['error-codes']))
                ) {
                    $this->logger->error('reCAPTCHA', 'secret is not valid.');
                }

                $errors['personal']['captcha'] = $this->snippets->getNamespace('plugins/MittwaldSecurityTools/reCAPTCHA')
                    ->get('captchaFailed', 'Captcha-Überprüfung fehlgeschlagen', TRUE);

            }

            $this->captchaChecked = TRUE;
        }

        if ($this->pluginConfig->minimumPasswordStrength > 0 && intval($postData['register']['personal']['accountmode']) !== 1) {

            $password = $postData['register']['personal']['password'];
            if ($this->passwordStrengthService->getScore($password) < $this->pluginConfig->minimumPasswordStrength) {
                $errors['personal']['password'] = $this->snippets->getNamespace('plugins/MittwaldSecurityTools/passwordStrength')
                    ->get('minimumPasswordStrengthFailed', 'Ihr Passwort ist nicht komplex genug.', TRUE);
            }
        }

        if (count($errors['personal']) > 0) {
            $controller->View()->assign('errors', $errors);
            $controller->View()->assign($postData);
            $controller->forward('index');
            return TRUE;
        }


        return NULL;
    }

    /**
     * adds the minimum password strength check to ajax password validation
     *
     * @param \Enlight_Event_EventArgs $args
     */
    public function enhanceAjaxPasswordValidation(\Enlight_Event_EventArgs $args)
    {
        /**
         * @var \Enlight_Controller_Action $controller
         */
        $controller = $args->getSubject();

        if ($controller->Request()->getActionName() == 'ajax_validate_password') {
            $data = $controller->Request()->getPost('register');
            $personal = $data['personal'];
            $password = $personal['password'];

            $passwordStrengthService = new PasswordStrengthService();
            $passwordStrengthScore = $passwordStrengthService->getScore($password);

            $bodyData = json_decode($controller->Response()->getBody(), true);

            if ($passwordStrengthScore < $this->pluginConfig->minimumPasswordStrength) {
                $error = $this->snippets->getNamespace('plugins/MittwaldSecurityTools/passwordStrength')
                    ->get('minimumPasswordStrengthFailed', 'Ihr Passwort ist nicht komplex genug.', TRUE);

                if (!$bodyData['password']) {
                    $bodyData['password'] = $error;
                }

                $controller->Response()->setBody(json_encode($bodyData));
            }
        }
    }

    /**
     * add our frontend templates for password strength and reCAPTCHA if necessary
     *
     * @param \Enlight_Event_EventArgs $args
     */
    public function addTemplates(\Enlight_Event_EventArgs $args)
    {
        if (!$this->pluginConfig->showPasswordStrengthForUserRegistration && !$this->pluginConfig->showRecaptchaForUserRegistration) {
            return;
        }

        /**
         * @var \Enlight_Controller_Action $controller
         */
        $controller = $args->getSubject();

        $view = $controller->View();
        $view->addTemplateDir($this->pluginPath . 'Views');

        if ($this->pluginConfig->showPasswordStrengthForUserRegistration) {
            $view->assign('mittwaldSecurityToolsMinimumPasswordStrength', $this->pluginConfig->minimumPasswordStrength);
            $view->extendsTemplate('frontend/plugin/mittwald_security_tools/password_strength/personal_fieldset.tpl');
        }

        if ($this->pluginConfig->showRecaptchaForUserRegistration) {
            if ($this->pluginConfig->recaptchaLanguageKey) {
                $view->assign('mittwaldSecurityToolsRecaptchaLanguageKey', $this->pluginConfig->recaptchaLanguageKey);
            }
            $view->assign('mittwaldSecurityToolsRecaptchaKey', $this->pluginConfig->recaptchaAPIKey);
            $view->extendsTemplate('frontend/plugin/mittwald_security_tools/customer_recaptcha/index.tpl');
        }


    }

    /**
     * add the templates for user manager / otp
     *
     * @param \Enlight_Event_EventArgs $args
     */
    public function addUserManagerTemplates(\Enlight_Event_EventArgs $args)
    {
        if (!$this->pluginConfig->useYubicoAuth) {
            return;
        }

        /**
         * @var \Enlight_Controller_Action $controller
         */
        $controller = $args->getSubject();

        if ($controller->Request()->getActionName() === 'load') {
            $view = $controller->View();
            $view->addTemplateDir($this->pluginPath . 'Views');
            $view->extendsTemplate('backend/mittwald_user_manager/view/emergency_password/grid.js');
            $view->extendsTemplate('backend/mittwald_user_manager/view/user/create.js');
            $view->extendsTemplate('backend/mittwald_user_manager/model/emergency_password.js');
            $view->extendsTemplate('backend/mittwald_user_manager/store/emergency_password.js');
        }
    }

    /**
     * add templates for login form / otp
     *
     * @param \Enlight_Event_EventArgs $args
     */
    public function addLoginTemplates(\Enlight_Event_EventArgs $args)
    {
        /**
         * @var \Enlight_Controller_Action $controller
         */
        $controller = $args->getSubject();

        if ($controller->Request()->getActionName() === 'load') {
            $view = $controller->View();
            $view->assign('MittwaldSecurityToolsUseYubikeyAuth', $this->pluginConfig->useYubicoAuth);
            $view->addTemplateDir($this->pluginPath . 'Views');
            $view->extendsTemplate('backend/mittwald_login/view/main/form.js');
        }
    }


    /**
     * add our custom backend menu template for our custom icon
     *
     * @param \Enlight_Event_EventArgs $args
     */
    public function addMenuTemplates(\Enlight_Event_EventArgs $args)
    {
        /**
         * @var \Enlight_Controller_Action $controller
         */
        $controller = $args->getSubject();

        $view = $controller->View();
        $view->extendsTemplate($this->pluginPath . '/Views/backend/index/header.tpl');
    }

    /**
     * checks if notifications should be sent
     */
    public function onCheckNotification()
    {
        $this->checkFailedLoginLimits(FALSE, $this->pluginConfig->mailNotificationForFailedFELoginsLimit);
        $this->checkFailedLoginLimits(TRUE, $this->pluginConfig->mailNotificationForFailedBELoginsLimit);
        return TRUE;
    }

    /**
     * save the failed FE login log
     *
     * @param \Enlight_Event_EventArgs $args
     * @return mixed
     */
    public function logFailedFELogin(\Enlight_Event_EventArgs $args)
    {
        if ($this->pluginConfig->logFailedFELogins || $this->pluginConfig->sendLockedAccountMail) {
            $mail = $args->getEmail();
            $errors = $args->getError();

            if (!$mail) {
                $mail = '';
            }

            if ($errors) {
                if($this->pluginConfig->logFailedFELogins) {
                    $this->saveFailedLogin($mail, FALSE);
                }

                if ($mail && $this->pluginConfig->sendLockedAccountMail) {
                    $sql = "
                            SELECT u.id, u.email, u.lockeduntil, u.failedlogins
                            FROM s_user AS u
                            INNER JOIN s_user_attributes AS a
                              ON u.id = a.userID
                            WHERE u.active = 1 AND u.accountmode = 0 AND u.lockeduntil IS NOT NULL AND u.email = ?
                        ";
                    $params = array($mail);

                    if(intval($this->pluginConfig->sendLockedAccountMailInterval) > 0)
                    {
                        $lockedAccountLimit = new \DateTime('-' . intval($this->pluginConfig->sendLockedAccountMailInterval) . ' minute');
                        $sql .= ' AND (a.mittwald_lastlockedaccountmail IS NULL OR a.mittwald_lastlockedaccountmail < ?)';
                        $params[] = $lockedAccountLimit->format("Y-m-d H:i:s");
                    }

                    $user = $this->db->fetchRow($sql, $params);

                    if($user) {
                        $templateMail = $this->templateMail->createMail('sLOCKEDACCOUNT', $user);
                        $templateMail->addTo($mail);
                        $templateMail->send();
                        $this->db->executeUpdate("
                            UPDATE s_user_attributes 
                            SET mittwald_lastlockedaccountmail = NOW()
                            WHERE userID = ?
                        ", array($user['id']));
                    }
                }
            }
        }
        return $args->getReturn();
    }


    /**
     * save the failed BE login log
     *
     * @param \Enlight_Event_EventArgs $args
     * @return mixed
     */
    public function logFailedBELogin(\Enlight_Event_EventArgs $args)
    {
        if ($this->pluginConfig->logFailedBELogins) {
            /**
             * @var \Shopware_Controllers_Backend_Login $controller
             */
            $controller = $args->getSubject();

            if (!$controller->View()->getAssign('success') && $controller->View()->getAssign('user')) {
                $this->saveFailedLogin($controller->View()->getAssign('user'), TRUE);
            }
        }
        return;
    }


    /**
     * @param string $username
     * @param bool $isBackend
     */
    protected function saveFailedLogin($username, $isBackend)
    {
        $failedLogin = new FailedLogin();
        $failedLogin->setCreated(new \DateTime());
        $failedLogin->setUsername($username);
        $failedLogin->setIsBackend($isBackend);
        $failedLogin->setIp($_SERVER['REMOTE_ADDR']);

        $this->modelManager->persist($failedLogin);
        $this->modelManager->flush($failedLogin);
    }


    /**
     * cron event listerener for log table cleanup
     *
     * @return bool
     */
    public function onLogCleanupCron()
    {
        if ($this->pluginConfig->cleanUpLogFailedBELogins) {
            $interval = intval($this->pluginConfig->cleanUpLogFailedBELoginsInterval);
            $this->cleanUpLogTable($interval, TRUE);
        }

        if ($this->pluginConfig->cleanUpLogFailedFELogins) {
            $interval = intval($this->pluginConfig->cleanUpLogFailedFELoginsInterval);
            $this->cleanUpLogTable($interval, FALSE);
        }

        return TRUE;
    }


    /**
     * @param $interval
     * @param $isBackend
     */
    protected function cleanUpLogTable($interval, $isBackend)
    {
        $relevantDateTime = new \DateTime('now - ' . $interval . ' days');

        $sql = "DELETE FROM s_plugin_mittwald_security_failed_logins
                    WHERE isBackend = " . ($isBackend ? 1 : 0) . "
                    AND UNIX_TIMESTAMP(created) < ?";

        $this->db->query($sql, array($relevantDateTime->getTimestamp()));
    }

    /**
     * @param $isBackend
     * @param $limit
     * @throws \Enlight_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    protected function checkFailedLoginLimits($isBackend, $limit)
    {
        $relevantDateTime = new \DateTime('now - 1 hour');

        $sql = "SELECT COUNT(id) as c FROM s_plugin_mittwald_security_failed_logins
                WHERE isBackend = " . ($isBackend ? 1 : 0) . "
                AND UNIX_TIMESTAMP(created) > ?";

        $result = $this->db->query($sql, array($relevantDateTime->getTimestamp()));

        $count = $result->fetchColumn();

        if ($count >= $limit) {
            $mail = $this->templateMail->createMail('sFAILEDLOGIN');
            $mail->addTo($this->shopConfig->get('sMAIL'));
            $mail->send();
        }
    }

}
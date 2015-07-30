<?php

namespace Shopware\Mittwald\SecurityTools\Subscribers;


use Enlight\Event\SubscriberInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\CustomModels\MittwaldSecurityTools\FailedLogin;
use Shopware\Mittwald\SecurityTools\Services\LogService;


/**
 * Class SecuritySubscriber
 * @package Shopware\Mittwald\SecurityTools
 *
 * @author  Philipp Mahlow <p.mahlow@mittwald.de>
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
     * @var \Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    protected $db;

    /**
     * @var \Shopware_Components_TemplateMail
     */
    protected $templateMail;


    public function __construct(\Enlight_Config $pluginConfig,
                                \Shopware_Components_Config $shopConfig,
                                ModelManager $modelManager,
                                \Enlight_Components_Db_Adapter_Pdo_Mysql $db,
                                \Shopware_Components_TemplateMail $templateMail)
    {
        $this->pluginConfig = $pluginConfig;

        $this->shopConfig = $shopConfig;

        $this->logger = new LogService($this->pluginConfig);

        $this->modelManager = $modelManager;

        $this->templateMail = $templateMail;

        $this->db = $db;
    }


    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Modules_Admin_Login_FilterResult' => 'logFailedFELogin',
            'Enlight_Controller_Action_PostDispatch_Backend_Login' => 'logFailedBELogin',
            'Shopware_CronJob_MittwaldSecurityCheckCleanUpFailedLogins' => 'onLogCleanupCron',
            'Shopware_CronJob_MittwaldSecurityCheckFailedLoginNotification' => 'onCheckNotification'
        ];
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
        if ($this->pluginConfig->logFailedFELogins) {
            $mail = $args->getEmail();
            $errors = $args->getError();

            if ($errors) {
                $this->saveFailedLogin($mail, FALSE);
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
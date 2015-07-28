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
    protected $config;


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



    public function __construct(\Enlight_Config $config, ModelManager $modelManager,
                                \Enlight_Components_Db_Adapter_Pdo_Mysql $db)
    {
        $this->config = $config;

        $this->logger = new LogService($this->config);

        $this->modelManager = $modelManager;

        $this->db = $db;
    }



    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Modules_Admin_Login_FilterResult'                 => 'logFailedFELogin',
            'Enlight_Controller_Action_PostDispatch_Backend_Login'      => 'logFailedBELogin',
            'Shopware_CronJob_MittwaldSecurityCheckCleanUpFailedLogins' => 'onLogCleanupCron'
        ];
    }



    /**
     * save the failed FE login log
     *
     * @param \Enlight_Event_EventArgs $args
     * @return mixed
     */
    public function logFailedFELogin(\Enlight_Event_EventArgs $args)
    {
        if ($this->config->logFailedFELogins)
        {
            $mail   = $args->getEmail();
            $errors = $args->getError();

            if ($errors)
            {
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
        if ($this->config->logFailedBELogins)
        {
            /**
             * @var \Shopware_Controllers_Backend_Login $controller
             */
            $controller = $args->getSubject();

            if (!$controller->View()->getAssign('success') && $controller->View()->getAssign('user'))
            {
                $this->saveFailedLogin($controller->View()->getAssign('user'), TRUE);
            }
        }
        return;
    }



    /**
     * @param string $username
     * @param bool   $isBackend
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
        if ($this->config->cleanUpLogFailedBELogins)
        {
            $interval = intval($this->config->cleanUpLogFailedBELoginsInterval);
            $this->cleanUpLogTable($interval, TRUE);
        }

        if ($this->config->cleanUpLogFailedFELogins)
        {
            $interval = intval($this->config->cleanUpLogFailedFELoginsInterval);
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

}
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



    public function __construct(\Enlight_Config $config, ModelManager $modelManager)
    {
        $this->config = $config;

        $this->logger = new LogService($this->config);

        $this->modelManager = $modelManager;
    }



    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Modules_Admin_Login_FilterResult'            => 'logFailedFELogin',
            'Enlight_Controller_Action_PostDispatch_Backend_Login' => 'logFailedBELogin'
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
        $mail = $args->getEmail();
        $errors = $args->getError();

        if($errors)
        {
            $this->saveFailedLogin($mail, FALSE);
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
        /**
         * @var \Shopware_Controllers_Backend_Login $controller
         */
        $controller = $args->getSubject();

        if (!$controller->View()->getAssign('success') && $controller->View()->getAssign('user'))
        {
            $this->saveFailedLogin($controller->View()->getAssign('user'), TRUE);
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



}
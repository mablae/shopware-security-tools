<?php

namespace Shopware\Mittwald\SecurityTools\Subscribers;


use Enlight\Event\SubscriberInterface;
use Shopware\Components\Model\ModelManager;
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
        ];
    }



}
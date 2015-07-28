<?php


/**
 * Class Shopware_Controllers_Backend_MittwaldSecurityTools
 *
 * @author Philipp Mahlow <p.mahlow@mittwald.de>
 */
class Shopware_Controllers_Backend_MittwaldSecurityTools extends Shopware_Controllers_Backend_ExtJs
{



    /**
     * @var \Shopware\Mittwald\SecurityTools\Services\Check\UsernameService
     */
    protected $usernameService;


    /**
     * @var \Shopware\Mittwald\SecurityTools\Services\Check\SslService
     */
    protected $sslService;


    /**
     * @var Enlight_Config
     */
    protected $config;


    /**
     * @var Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    protected $db;



    public function init()
    {
        $this->config          = Shopware()->Plugins()->Core()->MittwaldSecurityTools()->Config();
        $this->db              = Shopware()->Db();
        $this->usernameService = new \Shopware\Mittwald\SecurityTools\Services\Check\UsernameService($this->config,
                                                                                                     $this->db);
        $this->sslService      = new \Shopware\Mittwald\SecurityTools\Services\Check\SslService($this->config,
                                                                                                $this->db);

        parent::init();
    }



    public function doChecksAction()
    {
        $results = array_merge(
            $this->usernameService->getResult(),
            $this->sslService->getResult()
        );

        foreach ($results as $key => $result)
        {
            $results[$key]['id'] = ($key + 1);
        }

        $this->view->assign('data', $results);
    }


}
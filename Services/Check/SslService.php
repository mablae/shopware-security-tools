<?php

namespace Shopware\Mittwald\SecurityTools\Services\Check;


/**
 * Class LogService
 * @package Shopware\Mittwald\SecurityTools\Services\Check
 *
 * @author  Philipp Mahlow <p.mahlow@mittwald.de>
 *
 * Checks if SSL is enabled
 */
class SslService implements CheckServiceInterface
{


    /**
     * @var \Enlight_Config
     */
    protected $config;


    /**
     * @var \Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    protected $db;



    /**
     * @param \Enlight_Config                          $config
     * @param \Enlight_Components_Db_Adapter_Pdo_Mysql $db
     */
    public function __construct(\Enlight_Config $config, \Enlight_Components_Db_Adapter_Pdo_Mysql $db)
    {
        $this->config = $config;
        $this->db     = $db;
    }



    /**
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    public function getResult()
    {
        $sql = "SELECT `name` FROM s_core_shops WHERE active = 1 AND secure = 0 AND host IS NOT NULL";

        $result    = $this->db->query($sql, $this->badUsernames);
        $shopnames = $result->fetchAll();

        $return = [];
        if (count($shopnames) > 0)
        {
            foreach ($shopnames as $shopname)
            {
                $return[] = [
                    'reason' => 'noSSL',
                    'value'  => $shopname['name']
                ];
            }
        }

        return $return;
    }
}
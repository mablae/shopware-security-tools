<?php

namespace Shopware\Mittwald\SecurityTools\Services\Check;


/**
 * Class LogService
 * @package Shopware\Mittwald\SecurityTools\Services\Check
 *
 * @author  Philipp Mahlow <p.mahlow@mittwald.de>
 *
 * Checks if any unsafe standard usernames are configured and active
 */
class UsernameService implements CheckServiceInterface
{



    /**
     * @var array
     */
    protected $badUsernames = [
        'demo',
        'admin'
    ];


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
        $sql = "SELECT username FROM s_core_auth WHERE (active = 1) AND (1=0";
        for ($i = 0; $i < count($this->badUsernames); $i++)
        {
            $sql .= " OR username = ?";
        }

        $sql .= ")";

        $result    = $this->db->query($sql, $this->badUsernames);
        $usernames = $result->fetchAll();

        $return = [];
        if (count($usernames) > 0)
        {
            foreach ($usernames as $username)
            {
                $return[] = [
                    'reason' => 'badUsername',
                    'value'  => $username['username']
                ];
            }
        }

        return $return;
    }

}
<?php

namespace Shopware\Mittwald\SecurityTools\Services\Check;


/**
 * Class LogService
 * Checks if any unsafe standard usernames are configured and active
 *
 * @package Shopware\Mittwald\SecurityTools\Services\Check
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
class UsernameService implements CheckServiceInterface
{



    /**
     * @var array
     */
    protected $badUsernames = [
        'demo',
        'admin',
        'test'
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
     * inject all needed dependencies
     *
     * @param \Enlight_Config                          $config
     * @param \Enlight_Components_Db_Adapter_Pdo_Mysql $db
     */
    public function __construct(\Enlight_Config $config, \Enlight_Components_Db_Adapter_Pdo_Mysql $db)
    {
        $this->config = $config;
        $this->db     = $db;
    }



    /**
     * do the actual check
     *
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
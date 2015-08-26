<?php

namespace Shopware\Mittwald\SecurityTools\Services\Check;


/**
 * Class LogService
 * Checks if SSL is enabled
 *
 * @package Shopware\Mittwald\SecurityTools\Services\Check
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
     * inject all needed dependencies
     *
     * @param \Enlight_Config $config
     * @param \Enlight_Components_Db_Adapter_Pdo_Mysql $db
     */
    public function __construct(\Enlight_Config $config, \Enlight_Components_Db_Adapter_Pdo_Mysql $db)
    {
        $this->config = $config;
        $this->db = $db;
    }


    /**
     * do the actual check
     *
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    public function getResult()
    {
        $sql = "SELECT `name` FROM s_core_shops WHERE active = 1 AND secure = 0 AND host IS NOT NULL";

        $result = $this->db->query($sql, $this->badUsernames);
        $shopnames = $result->fetchAll();

        $return = [];
        if (count($shopnames) > 0) {
            foreach ($shopnames as $shopname) {
                $return[] = [
                    'reason' => 'noSSL',
                    'value' => $shopname['name']
                ];
            }
        }

        return $return;
    }
}
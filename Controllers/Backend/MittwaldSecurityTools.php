<?php


/**
 * Class Shopware_Controllers_Backend_MittwaldSecurityTools
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


    /**
     * initialize all dependencies
     */
    public function init()
    {
        $this->config = Shopware()->Plugins()->Core()->MittwaldSecurityTools()->Config();
        $this->db = Shopware()->Db();
        $this->usernameService = new \Shopware\Mittwald\SecurityTools\Services\Check\UsernameService($this->config,
            $this->db);
        $this->sslService = new \Shopware\Mittwald\SecurityTools\Services\Check\SslService($this->config,
            $this->db);

        parent::init();
    }


    /**
     * do the actual checks
     */
    public function doChecksAction()
    {
        $results = array_merge(
            $this->usernameService->getResult(),
            $this->sslService->getResult()
        );

        foreach ($results as $key => $result) {
            $results[$key]['id'] = ($key + 1);
        }

        $this->view->assign('data', $results);
    }


}
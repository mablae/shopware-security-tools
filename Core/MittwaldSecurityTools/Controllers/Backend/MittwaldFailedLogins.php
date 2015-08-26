<?php


/**
 * standard controller for the failed login model
 *
 * Class Shopware_Controllers_Backend_MittwaldFailedLogins
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
class Shopware_Controllers_Backend_MittwaldFailedLogins extends Shopware_Controllers_Backend_Application
{


    /**
     * @var string
     */
    protected $model = 'Shopware\CustomModels\MittwaldSecurityTools\FailedLogin';

    /**
     * @var string
     */
    protected $alias = 'failedLogins';


}
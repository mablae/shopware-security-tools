<?php
namespace Shopware\CustomModels\MittwaldSecurityTools;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="s_plugin_mittwald_security_failed_logins")
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
class FailedLogin extends ModelEntity
{


    /**
     * @ORM\Column(name="id", type="integer", nullable=FALSE)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var int
     */
    private $id;


    /**
     * @ORM\Column(name="username", type="string", nullable=FALSE)
     * @var string
     */
    private $username;


    /**
     * @ORM\Column(name="ip", type="string", nullable=FALSE)
     * @var string
     */
    private $ip;


    /**
     * @ORM\Column(name="created", type="datetime", nullable=FALSE)
     * @var \DateTime
     */
    private $created;


    /**
     * @ORM\Column(name="isBackend", type="boolean", nullable=FALSE)
     * @var boolean
     */
    private $isBackend;


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }


    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }


    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }


    /**
     * @param string $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }


    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }


    /**
     * @param \DateTime $created
     */
    public function setCreated(\DateTime $created)
    {
        $this->created = $created;
    }


    /**
     * @return boolean
     */
    public function getIsBackend()
    {
        return $this->isBackend;
    }


    /**
     * @param boolean $isBackend
     */
    public function setIsBackend($isBackend)
    {
        $this->isBackend = $isBackend;
    }


}
 
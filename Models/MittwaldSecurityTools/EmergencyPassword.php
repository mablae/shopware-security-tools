<?php
namespace Shopware\CustomModels\MittwaldSecurityTools;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
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
 *
 * @ORM\Entity
 * @ORM\Table(name="s_plugin_mittwald_security_yubikey_emergency_passwords")
 */
class EmergencyPassword extends ModelEntity
{


    /**
     * @ORM\Column(name="id", type="integer", nullable=FALSE)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var int
     */
    private $id;


    /**
     * @var \Shopware\Models\User\User
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\User\User")
     * @ORM\JoinColumn(name="userID", referencedColumnName="id")
     */
    private $user;


    /**
     * @ORM\Column(name="created", type="datetime", nullable=FALSE)
     * @var \DateTime
     */
    private $created;


    /**
     * @ORM\Column(name="deleted", type="boolean", nullable=FALSE)
     * @var boolean
     */
    private $isUsed;

    /**
     * @ORM\Column(name="password", type="string", nullable=FALSE)
     * @var string
     */
    private $password;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return \Shopware\Models\User\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param \Shopware\Models\User\User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
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
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return boolean
     */
    public function isIsUsed()
    {
        return $this->isUsed;
    }

    /**
     * @param boolean $isUsed
     */
    public function setIsUsed($isUsed)
    {
        $this->isUsed = $isUsed;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }


}
 
<?php
namespace Shopware\CustomModels\MittwaldSecurityTools;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="s_plugin_mittwald_security_yubikey_emergency_passwords")
 */
class EmergencyPassword extends ModelEntity
{


    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
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
     * @ORM\Column(name="created", type="datetime", nullable=false)
     * @var \DateTime
     */
    private $created;


    /**
     * @ORM\Column(name="deleted", type="boolean", nullable=false)
     * @var boolean
     */
    private $isUsed;

    /**
     * @ORM\Column(name="password", type="string", nullable=false)
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
 
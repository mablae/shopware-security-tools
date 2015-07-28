<?php
namespace Shopware\CustomModels\MittwaldSecurityTools;

use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="s_plugin_mittwald_security_failed_logins")
 */
class FailedLogin extends ModelEntity
{



    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @var int
     */
    private $id;


    /**
     * @ORM\Column(name="username", type="string", nullable=false)
     * @var string
     */
    private $username;


    /**
     * @ORM\Column(name="ip", type="string", nullable=false)
     * @var string
     */
    private $ip;


    /**
     * @ORM\Column(name="created", type="datetime", nullable=false)
     * @var \DateTime
     */
    private $created;


    /**
     * @ORM\Column(name="isBackend", type="boolean", nullable=false)
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
 
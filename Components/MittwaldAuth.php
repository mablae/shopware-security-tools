<?php
/**
 * decorator class for auth component
 * validates otp against yubico cloud
 *
 * @author Philipp Mahlow <p.mahlow@mittwald.de>
 *
 */
namespace Shopware\Mittwald\SecurityTools\Components;


use Enlight_Components_Db_Adapter_Pdo_Mysql;
use GuzzleHttp\ClientInterface;
use Shopware\Mittwald\SecurityTools\Services\LogService;
use Shopware_Components_Auth;
use Zend_Auth_Adapter_Interface;
use Zend_Auth_Result;
use Zend_Auth_Storage_Interface;

class MittwaldAuth extends Shopware_Components_Auth
{
    /**
     * @var Shopware_Components_Auth
     */
    protected $originalObject;

    /**
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * @var LogService
     */
    protected $logger;

    /**
     * @var Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    protected $db;

    /**
     * Get an instance from this object
     * @static
     * @return MittwaldAuth
     */
    public static function getInstance()
    {
        if (null === self::$_instance || !self::$_instance instanceof self) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * custom init method. we need some
     *
     * @param Shopware_Components_Auth $originalObject
     * @param Enlight_Components_Db_Adapter_Pdo_Mysql $db
     * @param ClientInterface $client
     * @param LogService $logger
     */
    public function init(Shopware_Components_Auth $originalObject, Enlight_Components_Db_Adapter_Pdo_Mysql $db,
                         ClientInterface $client, LogService $logger)
    {
        $this->originalObject = $originalObject;

        $this->httpClient = $client;

        $this->db = $db;

        $this->logger = $logger;

        self::$_instance = $this;
    }


    /**
     * Login method - iterate through all adapters and check for valid account
     *
     * @param string $username
     * @param string $password
     * @return \Zend_Auth_Result
     */
    public function login($username, $password)
    {
        $authResult = $this->originalObject->login($username, $password);

        if ($authResult->isValid()) {
            $request = Shopware()->Front()->Request();

            $otp = $request->getParam('yubikey');

            try {
                $sql = 'SELECT Mittwald_YubiKey FROM s_core_auth_attributes WHERE authID = ?';
                $result = $this->db->query($sql, array($this->originalObject->getIdentity()->id));

                $userKey = $result->fetchColumn();

                if (!isset($userKey) || strlen($userKey) == 0) {
                    $this->logger->debug('OTP', 'userkey is empty');
                    return $authResult;
                }

                if ($userKey !== substr($otp, 0, 12) || !$this->validateYubikeyOtp($otp)) {
                    $this->logger->debug('OTP', 'not valid');
                    $this->clearIdentity();
                    return new Zend_Auth_Result(-3, $authResult->getIdentity(), $authResult->getMessages());
                }
            } catch(\Exception $ex){
                $this->logger->debug('exception', $ex->getMessage());
                $this->clearIdentity();
                return new Zend_Auth_Result(-3, $authResult->getIdentity(), $authResult->getMessages());
            }
        }

        return $authResult;
    }

    /**
     * actual validation against yubico cloud
     *
     * @param string $otp
     * @return bool
     */
    protected function validateYubikeyOtp($otp)
    {
        $server_queue = array(
            'api.yubico.com',
            'api2.yubico.com',
            'api3.yubico.com',
            'api4.yubico.com',
            'api5.yubico.com',
        );
        shuffle($server_queue);

        $nonce = md5(uniqid(rand()));
        $params = http_build_query([
            'id' => 1,
            'otp' => $otp,
            'nonce' => $nonce,
            'sl' => 50,
            'timeout' => 5
        ]);

        $response = NULL;

        while (!empty($server_queue)) {
            $this->logger->debug('OTP', 'check server');
            $server = array_shift($server_queue);
            $uri = 'https://' . $server . '/wsapi/2.0/verify?' . $params;

            try {
                $response = $this->httpClient->get($uri);
                if (!empty($response)) {
                    break;
                } else {
                    continue;
                }
            } catch (\Exception $ex) {
                // No response, continue with the next server
                continue;
            }
        }

        // No server replied; we can't validate this OTP
        if (empty($response)) {
            return false;
        }

        // Parse response
        $lines = explode("\n", $response->getBody());
        $data = array();
        foreach ($lines as $line) {
            $line = trim($line);
            $parts = explode('=', $line, 2);
            if (count($parts) < 2) {
                continue;
            }
            $data[$parts[0]] = $parts[1];
        }
        // Validate the response - We need an OK message reply
        if ($data['status'] != 'OK') {
            return false;
        }
        // Validate the response - We need a confidence level over 50%
        if ($data['sl'] < 50) {
            return false;
        }
        // Validate the response - The OTP must match
        if ($data['otp'] != $otp) {
            return false;
        }
        // Validate the response - The token must match
        if ($data['nonce'] != $nonce) {
            return false;
        }
        return true;
    }

    /*
     * wrapper methods
     */

    /**
     * Returns the persistent storage handler
     *
     * Session storage is used by default unless a different storage adapter has been set.
     *
     * @return Zend_Auth_Storage_Interface
     */
    public function getStorage()
    {
        return $this->originalObject->getStorage();
    }

    /**
     * Sets the persistent storage handler
     *
     * @param  Zend_Auth_Storage_Interface $storage
     * @return \Zend_Auth Provides a fluent interface
     */
    public function setStorage(Zend_Auth_Storage_Interface $storage)
    {
        return $this->originalObject->setStorage($storage);
    }

    /**
     * Returns true if and only if an identity is available from storage
     *
     * @return boolean
     */
    public function hasIdentity()
    {
        return $this->originalObject->hasIdentity();
    }

    /**
     * Returns the identity from storage or null if no identity is available
     *
     * @return mixed|null
     */
    public function getIdentity()
    {
        return $this->originalObject->getIdentity();
    }

    /**
     * Clears the identity from persistent storage
     *
     * @return void
     */
    public function clearIdentity()
    {
        $this->originalObject->clearIdentity();
    }

    /**
     * Get all adapters or certain one
     *
     * @param null $index
     * @return array|Zend_Auth_Adapter_Interface
     */
    public function getAdapter($index = null)
    {
        return $this->originalObject->getAdapter($index);
    }

    /**
     * Add adapter to list
     * @param Zend_Auth_Adapter_Interface $adapter
     * @return Shopware_Components_Auth
     */
    public function addAdapter(Zend_Auth_Adapter_Interface $adapter)
    {
        return $this->originalObject->addAdapter($adapter);
    }

    /**
     * Set current active adapter
     *
     * @param $adapter
     * @return Shopware_Components_Auth
     */
    public function setBaseAdapter($adapter)
    {
        return $this->originalObject->setBaseAdapter($adapter);
    }

    /**
     * Get current active adapter
     *
     * @return Zend_Auth_Adapter_Interface
     */
    public function getBaseAdapter()
    {
        return $this->originalObject->getBaseAdapter();
    }

    /**
     * Do a authentication approve with a defined adapter
     *
     * @param null|Zend_Auth_Adapter_Interface $adapter
     * @return Zend_Auth_Result
     */
    public function authenticate(Zend_Auth_Adapter_Interface $adapter = null)
    {
        return $this->originalObject->authenticate($adapter);
    }

    /**
     * Refresh authentication - for example expire date -
     *
     * @param null|Zend_Auth_Adapter_Interface $adapter
     * @return mixed
     */
    public function refresh(Zend_Auth_Adapter_Interface $adapter = null)
    {
        return $this->originalObject->refresh($adapter);
    }

    /**
     * Sets the persistent storage handler
     *
     * @param Zend_Auth_Adapter_Interface $adapter
     * @return \Enlight_Components_Auth
     */
    public function setAdapter(Zend_Auth_Adapter_Interface $adapter)
    {
        return $this->originalObject->setAdapter($adapter);
    }


}
<?php
/**
 * decorator class for auth component
 * validates otp against yubico cloud
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
        if (NULL === self::$_instance || !self::$_instance instanceof self) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * custom init method. inject all the dependencies
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
        //check username password combination
        $authResult = $this->originalObject->login($username, $password);

        //do the other checks, if result is positive
        if ($authResult->isValid()) {
            $request = Shopware()->Front()->Request();

            //get otp param
            $otp = $request->getParam('yubikey');

            try {
                $sql = 'SELECT Mittwald_YubiKey FROM s_core_auth_attributes WHERE authID = ?';
                $result = $this->db->query($sql, array($this->originalObject->getIdentity()->id));

                $userKey = $result->fetchColumn();

                //there is no connected yubikey for this user. just return the original result.
                if (!isset($userKey) || strlen($userKey) == 0) {
                    $this->logger->debug('OTP', 'userkey is empty');
                    return $authResult;
                }

                $userKey = substr($userKey, 0, 12);

                //if the given token is not an emergency password or token is not valid anyway
                if (!$this->validateEmergencyPassword($otp) && ($userKey !== substr($otp, 0, 12) || !$this->validateYubikeyOtp($otp))) {
                    $this->logger->debug('OTP', 'not valid');
                    //login is not valid. return negative result.
                    $this->clearIdentity();
                    return new Zend_Auth_Result(-3, $authResult->getIdentity(), $authResult->getMessages());
                }
            } catch (\Exception $ex) {
                //something has gone wrong. return negative result.
                $this->logger->debug('exception', $ex->getMessage());
                $this->clearIdentity();

                return new Zend_Auth_Result(-3, $authResult->getIdentity(), $authResult->getMessages());
            }
        }

        return $authResult;
    }

    /**
     * actual validation against emergency passwords database
     *
     * @param string $otp
     * @return bool
     */
    protected function validateEmergencyPassword($otp)
    {
        $sql = 'SELECT id
                FROM s_plugin_mittwald_security_yubikey_emergency_passwords
                WHERE userID = ? AND password = ? AND deleted = 0';
        $result = $this->db->query($sql, array($this->originalObject->getIdentity()->id, $otp));

        $emergencyPasswordID = intval($result->fetchColumn());

        if ($emergencyPasswordID <= 0) {
            $this->logger->debug('emergency-password', 'invalid');
            return FALSE;
        }


        $sql = 'UPDATE s_plugin_mittwald_security_yubikey_emergency_passwords
                SET deleted = 1
                WHERE id = ?';
        $this->db->executeUpdate($sql, array($emergencyPasswordID));

        $this->logger->debug('emergency-password', 'success');

        return TRUE;
    }


    /**
     * actual validation against yubico cloud
     *
     * @param string $otp
     * @return bool
     */
    protected function validateYubikeyOtp($otp)
    {
        //all available yubico cloud servers
        $server_queue = array(
            'api.yubico.com',
            'api2.yubico.com',
            'api3.yubico.com',
            'api4.yubico.com',
            'api5.yubico.com',
        );
        shuffle($server_queue);

        //generate unique token
        $nonce = md5(uniqid(rand()));
        $params = http_build_query([
            'id' => 1,
            'otp' => $otp,
            'nonce' => $nonce,
            'sl' => 50,
            'timeout' => 5
        ]);

        $response = NULL;

        //try to get an result from at least one of the cloud servers
        while (!empty($server_queue)) {
            $this->logger->debug('OTP', 'check server');
            $server = array_shift($server_queue);
            $uri = 'https://' . $server . '/wsapi/2.0/verify?' . $params;

            try {
                $response = $this->httpClient->get($uri);
                if (!empty($response)) {
                    // one server responded. we can succeed do our verification
                    break;
                } else {
                    // something gone wrong. try next server.
                    continue;
                }
            } catch (\Exception $ex) {
                // No response, continue with the next server
                continue;
            }
        }

        // No server replied; we can't validate this OTP
        if (empty($response)) {
            return FALSE;
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
            return FALSE;
        }
        // Validate the response - We need a confidence level over 50%
        if ($data['sl'] < 50) {
            return FALSE;
        }
        // Validate the response - The OTP must match
        if ($data['otp'] != $otp) {
            return FALSE;
        }
        // Validate the response - The token must match
        if ($data['nonce'] != $nonce) {
            return FALSE;
        }
        return TRUE;
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
     * Returns TRUE if and only if an identity is available from storage
     *
     * @return boolean
     */
    public function hasIdentity()
    {
        return $this->originalObject->hasIdentity();
    }

    /**
     * Returns the identity from storage or NULL if no identity is available
     *
     * @return mixed|NULL
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
     * @param NULL $index
     * @return array|Zend_Auth_Adapter_Interface
     */
    public function getAdapter($index = NULL)
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
     * @param NULL|Zend_Auth_Adapter_Interface $adapter
     * @return Zend_Auth_Result
     */
    public function authenticate(Zend_Auth_Adapter_Interface $adapter = NULL)
    {
        return $this->originalObject->authenticate($adapter);
    }

    /**
     * Refresh authentication - for example expire date -
     *
     * @param NULL|Zend_Auth_Adapter_Interface $adapter
     * @return mixed
     */
    public function refresh(Zend_Auth_Adapter_Interface $adapter = NULL)
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
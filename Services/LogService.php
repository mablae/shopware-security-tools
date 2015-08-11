<?php

namespace Shopware\Mittwald\SecurityTools\Services;


/**
 * Class LogService
 * @package Shopware\Mittwald\SecurityTools\Services
 *
 * @author  Philipp Mahlow <p.mahlow@mittwald.de>
 *
 * Writes log files
 */
class LogService
{


    const ERROR_FILE = 'error.log';

    const DEBUG_FILE = 'debug.log';


    /**
     * @var string
     */
    protected $path;


    /**
     * @var \Enlight_Config
     */
    protected $config;

    /**
     * @param \Enlight_Config $config
     */
    public function __construct(\Enlight_Config $config)
    {
        $this->config = $config;

        $this->path = realpath(dirname(__FILE__)) . '/../logs/';

        if (!file_exists($this->path)) {
            mkdir($this->path);
        }
    }


    /**
     * @param string $topic
     * @param string $content
     */
    public function error($topic, $content)
    {
        $this->writeLogEntry($topic, $content, self::ERROR_FILE);
    }


    /**
     * @param string $topic
     * @param string $content
     */
    public function debug($topic, $content)
    {
        if ($this->config->debugMode) {
            $this->writeLogEntry($topic, $content, self::DEBUG_FILE);
        }
    }


    /**
     * @param string $topic
     * @param string $content
     * @param string $file
     */
    protected function writeLogEntry($topic, $content, $file)
    {
        $now = new \DateTime();
        $logEntry = $now->format('Y-m-d H:i:s') . ' - ' . $topic . "\n";
        $logEntry .= $content;
        $logEntry .= "--------------------- \n\n";

        file_put_contents($this->path . $file, $logEntry, FILE_APPEND);
    }


}
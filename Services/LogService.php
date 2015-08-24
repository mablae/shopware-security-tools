<?php

namespace Shopware\Mittwald\SecurityTools\Services;


/**
 * Class LogService
 * Writes log files
 *
 * @package Shopware\Mittwald\SecurityTools\Services *
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
     * initialize the logger. create the logs folder and inject config
     *
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
     * log for error messages
     *
     * @param string $topic
     * @param string $content
     */
    public function error($topic, $content)
    {
        $this->writeLogEntry($topic, $content, self::ERROR_FILE);
    }


    /**
     * log for debug messages. calls will be ignored, if debugMode is not active.
     *
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
     * internal function. writes the actual log entry.
     *
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
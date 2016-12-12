<?php

namespace Shopware\Mittwald\SecurityTools\Services;

/**
 * Class PasswordStrengthService
 * Server side implementation for password strength calculation
 *
 * @package Shopware\Mittwald\SecurityTools\Services *
 *
 * Copyright (C) 2016 Philipp Mahlow, Mittwald CM-Service GmbH & Co.KG
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
class PasswordStrengthService
{
    /**
     * minimum password length
     *
     * @var int
     */
    protected $minChars = 8;

    /**
     * significance value for each additional char
     *
     * @var int
     */
    protected $eachCharSignificance = 4;

    /**
     * bonus significance for big and small chars
     *
     * @var int
     */
    protected $bigAndSmallCharsSignificance = 8;

    /**
     * bonus significance for special chars
     *
     * @var int
     */
    protected $specialCharSignificance = 8;

    /**
     * bonus significance for numbers
     *
     * @var int
     */
    protected $numberSignificance = 8;

    /**
     * Calculates the strenth score for the given password
     *
     * @param string $password
     * @return int
     */
    public function getScore($password)
    {
        $maxSignificance = $this->minChars * $this->eachCharSignificance;

        $maximumStrengthScore = $maxSignificance + $this->bigAndSmallCharsSignificance + $this->specialCharSignificance
            + $this->numberSignificance;

        $charactersCount = strlen($password);

        $passwordSignificance = $charactersCount * $this->eachCharSignificance;

        if ($passwordSignificance > $maxSignificance) {
            $passwordSignificance = $maxSignificance;
        }

        // add bonus scores, if password matches minimum character count
        if ($charactersCount >= $this->minChars) {
            //bonus for big and small characters
            if (preg_match('/[a-z]/', $password) && preg_match('/[A-Z]/', $password)) {
                $passwordSignificance += $this->bigAndSmallCharsSignificance;
            }

            //bonus for special chars
            if (preg_match('/[\!\#\$\%\*\+,\-\.;\/\[\]_:\&\@\§\=]/', $password)) {
                $passwordSignificance += $this->specialCharSignificance;
            }

            //bonus for numbers
            if (preg_match('/[0-9]/', $password)) {
                $passwordSignificance += $this->numberSignificance;
            }
        }

        return intval(round(($passwordSignificance / $maximumStrengthScore) * 100));
    }
}
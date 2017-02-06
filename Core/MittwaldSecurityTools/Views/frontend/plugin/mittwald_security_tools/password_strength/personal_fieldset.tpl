{*
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
*}

{extends file="parent:frontend/register/personal_fieldset.tpl"}

{* add the password strength template *}
{block name="frontend_register_personal_fieldset_password_description" prepend}
    <div class="mwst--register--password--strength">
        {s name="password-strength-header"}Passwortstärke{/s}
        <div class="mwst--password--strength--bar--container" data-criterialist=".mwst--password--criteria-list">
            <div class="mwst--password--strength--bar mwst--password--strength--bar--0"></div>
            <div class="mwst--password--strength--bar mwst--password--strength--bar--1"></div>
            <div class="mwst--password--strength--bar mwst--password--strength--bar--2"></div>
            <div class="mwst--password--strength--bar mwst--password--strength--bar--3"></div>
        </div>

        {if $mittwaldSecurityToolsMinimumPasswordStrength > 0}
        <div class="mwst--password--strength--description">
            {s name="password-strength-description-intro"}Ihr Passwort muss mindestens {/s}
            {if $mittwaldSecurityToolsMinimumPasswordStrength == 100}
                {s name="password-strength-description-min-score-3"}drei {/s}
            {elseif $mittwaldSecurityToolsMinimumPasswordStrength >= 86}
                {s name="password-strength-description-min-score-2"}zwei {/s}
            {elseif $mittwaldSecurityToolsMinimumPasswordStrength >= 60}
                {s name="password-strength-description-min-score-1"}eines {/s}
            {/if}
            {s name="password-strength-description-outro"}der folgenden Kritierien erfüllen:{/s}
            <ul class="mwst--password--criteria-list">
                <li class="mwst--passwort--criteria--big-small">
                    {s name="password-strength-description-criteria-big-small"}Groß- und Kleinbuchstaben{/s}
                </li>
                <li class="mwst--passwort--criteria--numbers">
                    {s name="password-strength-description-criteria-numbers"}Zahlen{/s}
                </li>
                <li class="mwst--passwort--criteria--special-chars">
                    {s name="password-strength-description-criteria-special-chars"}Sonderzeichen (! # $ % * + , - . ; / [ ] _ : & @ § = ]){/s}
                </li>
            </ul>
        </div>
        {/if}
    </div>
{/block}
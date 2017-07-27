{*
*
* Copyright (C) 2017 Philipp Mahlow, Mittwald CM-Service GmbH & Co.KG
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

{extends file="parent:frontend/register/index.tpl"}

{* add googles recaptcha *}
{block name='frontend_register_index_form_submit'}
    {* Submit button *}
    <div class="register--action">
        <button type="submit" class="g-recaptcha register--submit btn is--primary is--large is--icon-right" name="Submit"
                data-sitekey="{$mittwaldSecurityToolsRecaptchaKey}" data-callback="recaptchaCallback">
            {s name="RegisterIndexNewActionSubmit" namespace="frontend/register/index"}{/s} <i class="icon--arrow-right"></i>
        </button>
    </div>
{/block}

{* add googles recaptcha script *}
{block name='frontend_index_header_javascript_jquery_lib' append}
    <script src='https://www.google.com/recaptcha/api.js{if $mittwaldSecurityToolsRecaptchaLanguageKey}?hl={$mittwaldSecurityToolsRecaptchaLanguageKey}{/if}'></script>
    <script>
        {literal}
        window.recaptchaCallback = function(token) {
            document.getElementsByClassName('register--form')[0].submit();
        };
        {/literal}
    </script>
{/block}
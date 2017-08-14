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

{* add googles recaptcha *}
{block name="frontend_newsletter_form_submit"}
    <div class="newsletter--action">
        <button type="submit" class="g-recaptcha btn is--primary right is--icon-right" name="{s name="sNewsletterButton"}{/s}"
                data-sitekey="{$mittwaldSecurityToolsRecaptchaKey}" data-callback="recaptchaNewsletterFormCallback">
            {s name="sNewsletterButton" namespace="frontend/newsletter/index"}{/s}
            <i class="icon--arrow-right"></i>
        </button>
    </div>
{/block}
{block name="frontend_index_footer_column_newsletter_form_submit"}
    <button type="submit" class="g-recaptcha newsletter--button btn"
            data-sitekey="{$mittwaldSecurityToolsRecaptchaKey}" data-callback="recaptchaNewsletterFoooterCallback">
        <i class="icon--mail"></i> <span class="button--text">{s name='IndexFooterNewsletterSubmit' namespace="frontend/index/menu_footer"}{/s}</span>
    </button>
{/block}

{* add googles recaptcha script *}
{block name='frontend_index_header_javascript_jquery_lib' append}
    <script src='https://www.google.com/recaptcha/api.js{if $mittwaldSecurityToolsRecaptchaLanguageKey}?hl={$mittwaldSecurityToolsRecaptchaLanguageKey}{/if}'></script>
    <script>
        {literal}
        window.recaptchaNewsletterFormCallback = function(token) {
            document.getElementsByTagName('form')[1].submit();
        };


        window.recaptchaNewsletterFoooterCallback = function(token) {
            document.getElementsByClassName('newsletter--form')[0].submit();
        };
        {/literal}
    </script>
{/block}


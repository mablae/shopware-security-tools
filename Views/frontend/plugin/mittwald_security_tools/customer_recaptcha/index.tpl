{extends file="parent:frontend/register/index.tpl"}

{* add googles recaptcha *}
{block name='frontend_register_index_form_submit' prepend}
    <div class="panel">
        <div class="panel--body is--wide">
            <div class="g-recaptcha" data-sitekey="{$mittwaldSecurityToolsRecaptchaKey}"></div>
        </div>
    </div>
{/block}

{* add googles recaptcha script *}
{block name='frontend_index_header_javascript_jquery_lib' append}
    <script src='https://www.google.com/recaptcha/api.js'></script>
{/block}
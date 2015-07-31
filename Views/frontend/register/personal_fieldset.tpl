{extends file="parent:frontend/register/personal_fieldset.tpl"}

{block name="frontend_register_personal_fieldset_password_description" prepend}
    <div class="mwst--register--password--strength">
        {s name="password-strength-header"}Passwortstärke{/s}
        <div class="mwst--password--strength--bar--container">
            <div class="mwst--password--strength--bar mwst--password--strength--bar--0"></div>
            <div class="mwst--password--strength--bar mwst--password--strength--bar--1"></div>
            <div class="mwst--password--strength--bar mwst--password--strength--bar--2"></div>
            <div class="mwst--password--strength--bar mwst--password--strength--bar--3"></div>
        </div>
    </div>
{/block}
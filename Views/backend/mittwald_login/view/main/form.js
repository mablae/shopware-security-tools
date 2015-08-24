//{namespace name=backend/login/view/main}


//{block name="backend/login/view/main/form" append}
Ext.define('Shopware.apps.MittwaldSecurityTools.view.main.Form', {
    override: 'Shopware.apps.Login.view.main.Form',
    initComponent: function(){
        var me = this;
        me.callParent(arguments);

        //{if $MittwaldSecurityToolsUseYubikeyAuth}

            me.otpField = Ext.create('Ext.form.field.Text', {
                inputType: 'password',
                name: 'yubikey',
                allowBlank: true,
                emptyText: '{s name=field/otp}One Time Password{/s}'
            });

            me.items.insert(3,me.otpField);

        //{/if}

    }
});
//{/block}
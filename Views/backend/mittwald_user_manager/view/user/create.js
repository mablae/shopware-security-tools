//{extends file="parent:backend/user_manager/view/user/create.js"}

//{namespace name=backend/user_manager/view/main}

//{block name="backend/user_manager/view/user/create" append}
Ext.define('Shopware.apps.MittwaldSecurityTools.view.user.Create', {

    override: 'Shopware.apps.UserManager.view.user.Create',
    getUserTab: function(){
        var me = this;
        var tabPanel = me.callParent(arguments);
        me.yubikeyField = Ext.create('Ext.form.field.Text', {
            fieldLabel: 'YubiKey Secret'
        });


        tabPanel.add(
            Ext.create('Ext.form.Panel', {
                title: '2-Faktor Authentifizierung',
                items: [
                    Ext.create('Ext.form.FieldSet',
                        {
                            title: 'Yubikey',
                            bodyPadding : 10,
                            defaults    : {
                                labelWidth: '155px',
                                labelStyle: 'font-weight: 700; text-align: right;'
                            },
                            items: [
                                Ext.create('Ext.draw.Text', {
                                    text: 'Erklärung...'
                                }),
                                me.yubikeyField
                            ]
                        })
                ],
                border      : false,
                layout      : 'anchor',
                autoScroll:true,
                bodyPadding : 10,
                defaults    : {
                    labelWidth: '155px',
                    labelStyle: 'font-weight: 700; text-align: right;'
                }
            })
        );

        var attributeModel = me.record.getAttributes().getAt(0);

        //create an empty attribute model if necessary
        if(!attributeModel)
        {
            attributeModel = Ext.create('Shopware.apps.UserManager.model.Attribute');
            attributeModel.set('userID', me.record.get('id'));
            me.record.getAttributes().add(attributeModel);
        }

        me.on({
            'saveUser': function(record, formPanel){
                if(me.yubikeyField.getValue() && me.yubikeyField.getValue().length == 44)
                {
                    attributeModel.set('mittwaldYubiKey', me.yubikeyField.getValue().substring(0,12));
                }
            }
        });

        return tabPanel;
    }
});
//{/block}
//{block name="backend/mittwald_security_tools/main"}
Ext.define('Shopware.apps.MittwaldSecurityTools.store.CheckResult', {
    extend:'Ext.data.Store',
    model:'Shopware.apps.MittwaldSecurityTools.model.CheckResult',
    remoteSort:false,
    remoteFilter:false,
    pageSize:50,
    batch:true,
    autoLoad: true
});
//{/block}

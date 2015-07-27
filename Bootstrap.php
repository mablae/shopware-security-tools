<?php


/**
 * Class Shopware_Plugins_Frontend_MittwaldSecurityTools_Bootstrap
 *
 * @author Philipp Mahlow <p.mahlow@mittwald.de>
 */
class Shopware_Plugins_Core_MittwaldSecurityTools_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{



    /**
     * @var bool
     */
    protected $isInitialized = FALSE;



    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Mittwald Security Tools';
    }



    /**
     * @return string
     */
    public function getVersion()
    {
        return "1.0.0";
    }



    /**
     * @return array
     */
    public function getInfo()
    {
        return array(
            'version'     => $this->getVersion(),
            'copyright'   => 'Copyright (c) 2015, Mittwald CM-Service GmbH & Co.KG',
            'label'       => $this->getLabel(),
            'description' => file_get_contents($this->Path() . 'info.txt'),
            'link'        => 'http://www.mittwald.de',
            'author'      => 'Philipp Mahlow | Mittwald CM-Service GmbH & Co.KG'
        );
    }



    /**
     * install our plugin and call further init methods
     *
     * @return array|bool
     */
    public function install()
    {
        try
        {
            //normal call
            $this->subscribeEvent(
                'Enlight_Controller_Front_StartDispatch',
                'onStartDispatch'
            );

            /*
             * CLI-call will not trigger Enlight_Controller_Front_StartDispatch Event.
             * Use Enlight_Bootstrap_InitResource_Cron Event.
             */
            $this->subscribeEvent(
                'Enlight_Bootstrap_InitResource_Cron',
                'onStartDispatch'
            );

            $this->registerController('Backend', 'MittwaldSecurityTools');

            $this->createMenuItem(array(
                                      'label'      => 'Mittwald Security Tools',
                                      'controller' => 'MittwaldSecurityTools',
                                      'class'      => 'sprite-box-zipper',
                                      'action'     => 'Index',
                                      'active'     => 1,
                                      'parent'     => $this->Menu()->findOneBy('label', 'Einstellungen')
                                  ));

            $this->createCronJob('Security Check', 'MittwaldSecurityCheck', 1);

            return TRUE;
        }
        catch (Exception $ex)
        {
            return [
                'success' => FALSE,
                'message' => $ex->getMessage()
            ];
        }
    }



    /**
     * Listener for Enlight_Controller_Front_StartDispatch
     * Registers our namespace and adds our SecuritySubscriber
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onStartDispatch(Enlight_Event_EventArgs $args)
    {
        /*
         * prevent double initialization
         */
        if (!$this->isInitialized)
        {
            $this->Application()->Loader()->registerNamespace(
                'Shopware\\Mittwald\\SecurityTools',
                $this->Path()
            );

            $subscriber = new \Shopware\Mittwald\SecurityTools\Subscribers\SecuritySubscriber(
                $this->Config(),
                $this->get('models')
            );

            $this->Application()->Events()->addSubscriber($subscriber);

            $this->isInitialized = TRUE;
        }
    }



    /**
     * do nothing atm
     *
     * @return bool
     */
    public function uninstall()
    {

        return TRUE;
    }

}
<?php 
/**
 * Helper class for frontend needs
 * 
 * @author Miroslav Petrov <miroslav.petrov@modacom.com>
 * @package Modacom_Slide
 */
class Modacom_Slide_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Stores slide from registry
     * 
     * @var Modacom_Slide_Model_Slide
     */
    protected $_slideInstance;

    /**
    * Return current slide instance from the registry
    *
    * @return Modacom_Slide_Model_Slide
    */
    public function getSlideInstance()
    {
        if (!$this->_slideInstance) {
            $this->_slideInstance = Mage::registry('slide_data');
            if (!$this->_slideInstance) {
                Mage::throwException($this->__("Slide item instance doesn't exist in Registry"));
            }
        }
        return $this->_slideInstance;
    }

    /**
     * Check if there is slide for adding additional class
     *
     * @return string
     */
    public function getClass()
    {
        $string = Mage::app()->getRequest()->getRequestString();
        $urlKey = substr($string, 1);
        if(!$urlKey) $urlKey = 'home';
        $slides = Mage::getModel('modacom_slide/slide')->getCollection();
        $slides->addFieldToFilter('url_key', $urlKey);
        $slides->addFieldToFilter('status', 1); 
        return (count($slides)) ? ' hasBanner' : '';
    }

    public function hasEnableCarousel()
    {
        return (bool)Mage::getStoreConfig('modacom_slide_tab/settings/enable_multiple_slides');
    }

    /**
     * Get mode enabled
     * 
     * @return string
     */
    public function getMode()
    {
        return $this->hasEnableCarousel() ? "carousel" : "banner";
    }

    /**
     * Check if extension is enabled
     * 
     * @return boolean
     */
    public function isEnabled()
    {
        return (bool)Mage::getStoreConfig('modacom_slide_tab/settings/enable');
    }

    /**
     * Check if arrow controls are enabled
     * 
     * @return boolean
     */
    public function hasShowArrows()
    {
        return (bool)Mage::getStoreConfig('modacom_slide_tab/settings/show_arrows');
    }

    /**
     * Check if dot controls are enabled
     * 
     * @return boolean
     */
    public function hasShowDots()
    {
        return (bool)Mage::getStoreConfig('modacom_slide_tab/settings/show_dots');
    }
}
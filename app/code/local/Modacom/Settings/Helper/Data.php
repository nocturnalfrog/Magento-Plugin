<?php 
/**
 * Helper class for site settings
 * 
 * @category    Modacom_Settings
 * @author      Miroslav Petrov <miroslav.petrov@modacom.com>
 */
class Modacom_Settings_Helper_Data extends Mage_Core_Helper_Abstract
{   
    /**
     * configuration path for System -> Configuration -> Site Settings
     */
    const XML_PATH_FACEBOOK       = 'modacom_settings_tab/settings/facebook';
    const XML_PATH_TUMBLR       = 'modacom_settings_tab/settings/tumblr';
    const XML_PATH_TWITTER      = 'modacom_settings_tab/settings/twitter';
    const XML_PATH_INSTAGRAM    = 'modacom_settings_tab/settings/instagram';

    /**
     * Returns tumblr page url
     * @return string
     */
    public function getTumblrUrl()
    {
        return Mage::getStoreConfig(self::XML_PATH_TUMBLR);
    }

    /**
     * Returns facebook page url
     * @return string
     */
    public function getFacebook()
    {
        return Mage::getStoreConfig(self::XML_PATH_FACEBOOK);
    }

    public function getTwitterUrl()
    {
        return Mage::getStoreConfig(self::XML_PATH_TWITTER);
    }

    public function getInstagramUrl()
    {
        return Mage::getStoreConfig(self::XML_PATH_INSTAGRAM);
    }

}
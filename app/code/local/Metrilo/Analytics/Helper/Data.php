<?php 
/**
 * Helper class for metrilo properties
 * 
 * @author Miroslav Petrov <miro91tn@gmail.com>
 */
class Metrilo_Analytics_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Get session instance
     * 
     * @return Mage_Core_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('core/session');
    }

    /**
     * Get API Token from system configuration
     * 
     * @return string
     */
    public function getApiToken()
    {
        return Mage::getStoreConfig('metrilo_analytics_settings/settings/api_key');
    }

    /**
     * Get API Secret from system configuration
     * 
     * @return string
     */
    public function getApiSecret()
    {
        return Mage::getStoreConfig('metrilo_analytics_settings/settings/api_secret');
    }

    public function addEvent($method, $type, $data)
    {
        $events = (array)$this->getSession()->getData(Metrilo_Analytics_Block_Head::DATA_TAG);
        $events[] = array(
            'method' => $method,
            'type' => $type,
            'data' => $data
        );
        $this->getSession()->setData(Metrilo_Analytics_Block_Head::DATA_TAG, $events);
    }
}
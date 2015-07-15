<?php 
/**
 * Helper class for metrilo properties
 * 
 * @author Miroslav Petrov <miro91tn@gmail.com>
 */
class Metrilo_Analytics_Helper_Data extends Mage_Core_Helper_Abstract
{

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
}
<?php
/**
 * Import model which collect all previous orders
 *
 * @author Miroslav Petrov <miro91tn@gmail.com>
 */
class Metrilo_Analytics_Model_Import extends Mage_Core_Model_Abstract
{
    private $_ordersTotal = 0;
    private $_totalChunks = 0;
    private $_chunkItems  = 15;

    /**
     * Prepare all order ids
     *
     * @return void
     */
    public function _construct()
    {
        // prepare to fetch all orders
        $this->_ordersTotal = $this->_getOrderQuery()->getSize();
        Mage::log('Orders total: '.$this->_ordersTotal, null, 'Metrilo_Analytics.log');
        $this->_totalChunks = (int)ceil($this->_ordersTotal / $this->_chunkItems);
    }

    /**
     * Get chunk orders
     *
     * @param  int
     * @return Varien_Data_Collection
     */
    public function getOrders($chunkId)
    {
        return $this->_getOrderQuery()
                    ->setPageSize($this->_chunkItems)
                    ->setCurPage($chunkId + 1);
    }

    /**
     * Chenks array
     *
     * @return array
     */
    public function getChunks()
    {
        return $this->_totalChunks;
    }

    private function _getOrderQuery()
    {
        Mage::log('Store id: '.$this->_getStoreId(), null, 'Metrilo_Analytics.log');
        return Mage::getModel('sales/order')
                    ->getCollection()
                    ->addAttributeToFilter('store_id', $this->_getStoreId());
    }

    private function _getStoreId()
    {
        $helper  = Mage::helper('metrilo_analytics');
        $request = Mage::app()->getRequest();

        return $helper->getStoreId($request);
    }
}

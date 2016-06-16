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

    }

    /**
     * Get chunk orders
     *
     * @param  int
     * @return Varien_Data_Collection
     */
    public function getOrders($storeId, $chunkId)
    {
        return $this->_getOrderQuery($storeId)
                    ->setPageSize($this->_chunkItems)
                    ->setCurPage($chunkId + 1);
    }

    /**
     * Chenks array
     *
     * @return array
     */
    public function getChunks($storeId)
    {
        $storeTotal = $this->_getOrderQuery($storeId)->getSize();

        return (int)ceil($storeTotal / $this->_chunkItems);
    }

    /**
    * Get contextual store id
    *
    * @return int
    */
    public function getStoreId()
    {
        $helper  = Mage::helper('metrilo_analytics');
        $request = Mage::app()->getRequest();

        return $helper->getStoreId($request);
    }

    private function _getOrderQuery($storeId)
    {
        return Mage::getModel('sales/order')
                    ->getCollection()
                    ->addAttributeToFilter('store_id', $storeId);
    }
}

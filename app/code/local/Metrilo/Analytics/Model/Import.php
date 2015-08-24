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
    private $_chunkItems = 15;

    /**
     * Prepare all order ids
     *
     * @return void
     */
    public function _construct()
    {
        // prepare to fetch all orders
        $orders = Mage::getModel('sales/order')->getCollection();
        $this->_ordersTotal = $orders->count();
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
        $orders = Mage::getModel('sales/order')
                    ->getCollection()
                    ->setPageSize($this->_chunkItems);
        return $orders->setCurPage($chunkId + 1);
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
}
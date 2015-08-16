<?php
/**
 * Import model which collect all previous orders
 *
 * @author Miroslav Petrov <miro91tn@gmail.com>
 */
class Metrilo_Analytics_Model_Import extends Mage_Core_Model_Abstract
{
    private $_orders = array();
    private $_ordersTotal = 0;
    private $_chunks = array();
    private $_totalChunks = 0;

    /**
     * Prepare all order ids
     *
     * @return void
     */
    public function _construct()
    {
        // prepare to fetch all orders
        $orders = Mage::getModel('sales/order')->getCollection();
        foreach($orders as $order){
            array_push($this->_orders, $order->getIncrementId());
        }

        $this->_ordersTotal = count($this->_orders);
        $this->_prepareOrderChunks();
    }

    /**
     * Prepare order chunks
     *
     * @return void
     */
    private function _prepareOrderChunks()
    {
        $chunks = array();
        $current_chunk = 0;
        foreach($this->_orders as $order_id){
            if(!isset($chunks[$current_chunk])){
                $chunks[$current_chunk] = array();
            }
            $chunks[$current_chunk][] = $order_id;
            if(count($chunks[$current_chunk]) >= 15){
                $current_chunk++;
            }
        }
        $this->_chunks = $chunks;
        $this->_totalChunks = count($chunks);
    }

    /**
     * Chenks array
     *
     * @return array
     */
    public function getChunks()
    {
        return $this->_chunks;
    }
}
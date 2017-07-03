<?php
/**
 * Ajax controller for sending orders to metrilo
 *
 * @author Miroslav Petrov <miro91tn@gmail.com>
 */
class Metrilo_Analytics_Adminhtml_AjaxController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Import order chunks
     *
     * @return void
     */
    public function indexAction()
    {
        $result = array();
        $result['success'] = false;
        $helper = Mage::helper('metrilo_analytics');
        try {
            $import = Mage::getSingleton('metrilo_analytics/import');
            $storeId = (int)$this->getRequest()->getParam('store_id');
            $chunkId = (int)$this->getRequest()->getParam('chunk_id');
            // Get orders from the Database
            $orders = $import->getOrders($storeId, $chunkId);
            // Send orders via API helper method (last parameter shows synchronity)
            $helper->callBatchApi($storeId, $orders, false);
            $result['success'] = true;
        } catch (Exception $e) {
            Mage::logException($e);
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    protected function _isAllowed()
    {
	return true;
    }
}

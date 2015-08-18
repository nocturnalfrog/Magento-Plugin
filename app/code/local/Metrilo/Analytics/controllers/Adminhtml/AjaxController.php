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
            $orderIds = $this->getRequest()->getParam('orders');
            foreach ($orderIds as $incrementId) {
                $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
                if ($order->getId()) {
                    $orderDetails = $helper->prepareOrderDetails($order);

                    $callParameters = false;

                    // check if order has customer IP in it
                    $ip = $order->getRemoteIp();
                    if($ip){
                        $callParameters = array('use_ip' => $ip);
                    }

                    $time = false;
                    if ($order->getCreatedAtStoreDate()) {
                        $time = $order->getCreatedAtStoreDate()->getTimestamp() * 1000;
                    }

                    $identityData = array(
                        'email'         => $order->getCustomerEmail(),
                        'first_name'    => $order->getBillingAddress()->getFirstname(),
                        'last_name'     => $order->getBillingAddress()->getLastname(),
                        'name'          => $order->getBillingAddress()->getName(),
                    );
                    $helper->callApi($identityData['email'], 'order', $orderDetails, $identityData, $time, $callParameters);
                }
            }
            $result['success'] = true;
        } catch (Exception $e) {
            Mage::logException($e);
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}
<?php 
/**
 * Catch events and track them to metrilo api
 * 
 * @author Miroslav Petrov <miro91tn@gmail.com>
 */
class Metrilo_Analytics_Model_Observer
{
    /**
     * Identify customer after login
     * 
     * @param  Varien_Event_Observer $observer
     * @return void
     */
    public function customerLogin(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('metrilo_analytics');
        $customer = $observer->getEvent()->getCustomer();
        $data = array(
            'id' => $customer->getId(),
            'params' => array(
                'email' => $customer->getEmail(),
                'name' => $customer->getName()
            )
        );
        $helper->addEvent('identify', $data);
    }
}
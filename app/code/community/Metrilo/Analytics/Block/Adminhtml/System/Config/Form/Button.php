<?php
/**
 * Button widget class
 * Add import model and render button view
 *
 * @author Miroslav Petrov <miro91tn@gmail.com>
 */
class Metrilo_Analytics_Block_Adminhtml_System_Config_Form_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Set template
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('metrilo/system/config/button.phtml');
    }

    /**
     * Get import instance
     *
     * @return Metrilo_Analytics_Model_Import
     */
    public function getImport()
    {
        return Mage::getSingleton('metrilo_analytics/import');
    }

    /**
     * Render metrilo js if module is enabled
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = parent::_toHtml();
        $helper = Mage::helper('metrilo_analytics');
        if($helper->isEnabled() && $helper->getApiToken() && $helper->getApiSecret())
            return $html;
    }

    /**
    * Return element html
    *
    * @param  Varien_Data_Form_Element_Abstract $element
    * @return string
    */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }

    /**
    * Return ajax url for button
    *
    * @return string
    */
    public function getAjaxUrl()
    {
        return Mage::helper('adminhtml')->getUrl("metrilo_analytics/adminhtml_ajax", array('isAjax'=> true));
    }

    /**
    * Generate button html
    *
    * @return string
    */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
        ->setData(array(
            'id'        => 'metrilo_button',
            'label'     => $this->helper('adminhtml')->__('Import orders'),
            'onclick'   => 'javascript:import_metrilo(); return false;'
            ));

        return $button->toHtml();
    }
}
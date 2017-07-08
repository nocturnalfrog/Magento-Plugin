<?php
/**
 * Display a hint at the top of settings group
 *
 * @author Marush Denchev <avreon@gmail.com>
 */
class Metrilo_Analytics_Block_Adminhtml_System_Config_Form_Hint extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_template = 'metrilo/system/config/hint.phtml';

    public function __construct()
    {
        parent::__construct();
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->toHtml();
    }

    public function getModuleVersion()
    {
        return (string) Mage::getConfig()->getNode('modules/Metrilo_Analytics/version');
    }
}

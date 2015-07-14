<?php 
/**
 * @author Miroslav Petrov <miroslav.petrov@modacom.com>
 */
class Modacom_Slide_Block_Adminhtml_Slide_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
    * Initialise edit form container
    * 
    * @return  void
    */
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'modacom_slide';
        $this->_controller = 'adminhtml_slide';
        parent::__construct();
        
        // Add save buttons
        $this->_updateButton('save', 'label', Mage::helper('modacom_slide')->__('Save Slide'));
        $this->_addButton('saveandcontinue',
            array(
                'label' => Mage::helper('modacom_slide')->__('Save and Continue Edit'),
                'onclick' => 'saveAndContinueEdit()',
                'class' => 'save',
            ), -100
        );
        // add delete button
        $this->_updateButton('delete', 'label', Mage::helper('modacom_slide')->__('Delete Slide'));
        
        // Remove Hide/show editor button and create function for save and continue button
        $this->_formScripts[] = "
            $$('span.delete-image')[0].remove(); // remove delete checkbox
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }";
    }

    /**
    * Retrieve text for header element depending on loaded page
    *
    * @return string
    */
    public function getHeaderText()
    {
        $model = Mage::helper('modacom_slide')->getSlideInstance();
        if ($model->getId()) {
            return Mage::helper('modacom_slide')->__("Edit Slide '%s'", $this->escapeHtml($model->getTitle()));
        } else {
            return Mage::helper('modacom_slide')->__('New Slide');
        }
    }
}
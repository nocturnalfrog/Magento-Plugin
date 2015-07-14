<?php 
/**
 * Displaying form widgets in form tab
 * 
 * @author Miroslav Petrov <miroslav.petrov@modacom.com>
 */
class Modacom_Slide_Block_Adminhtml_Slide_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Add TinyMCE editor
     * @return Modacom_LandingPages_Block_Adminhtml_Landingpages_Edit_Tab_Form
     */
    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        return parent::_prepareLayout();;
    }

    /**
     * Prepare form by adding fields
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {       
        $landingPageData = Mage::registry('slide_data');
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('slide_form', array('legend' => Mage::helper('modacom_slide')->__('Item information')));

        $fieldset->addField('title', 'text', array(
            'label'     => Mage::helper('modacom_slide')->__('Title'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'title',
        ));

        $fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('modacom_slide')->__('Status'),
            'name'      => 'status',
            'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('modacom_slide')->__('Enabled'),
              ),
              array(
                  'value'     => 0,
                  'label'     => Mage::helper('modacom_slide')->__('Disabled'),
              ),
            )
        ));

        $fieldset->addField('url_key', 'text', array(
            'label'     => Mage::helper('modacom_slide')->__('Key'),
            'title'     => Mage::helper('modacom_slide')->__('Key'),
            'name'      => 'url_key',
            'required'  => true,
            'note'      => "For homepage set 'home'. For other pages set the url. Example: journal"
        ));

        $fieldset->addField('image', 'image', array(
            'label'     => Mage::helper('modacom_slide')->__('Image'),
            'title'     => Mage::helper('modacom_slide')->__('Image'),
            'name'      => 'image',
            'required'  => true,
            'note'      => 'minimum height: 360px'
        ));

        $fieldset->addField('url', 'text', array(
            'label'     => Mage::helper('modacom_slide')->__('Image link'),
            'title'     => Mage::helper('modacom_slide')->__('Image link'),
            'name'      => 'url',
            'required'  => false,
            'note'      => 'Leave it empty for images without link'
        ));

        $fieldset->addField('new_window', 'select', array(
            'label'     => Mage::helper('modacom_slide')->__('Open link in new window?'),
            'name'      => 'new_window',
            'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('modacom_slide')->__('Yes'),
              ),
              array(
                  'value'     => 0,
                  'label'     => Mage::helper('modacom_slide')->__('No'),
              ),
            )
        ));
        $helper = Mage::helper('modacom_slide');
        if ($helper->getMode() == 'carousel') {
            $fieldset->addField('position', 'text', array(
                'label'     => Mage::helper('modacom_slide')->__('Slide position'),
                'title'     => Mage::helper('modacom_slide')->__('Slide position'),
                'name'      => 'position',
                'required'  => false,
                'note'      => 'Position for sorting slides for same "key"'
            ));
        }

        if ( Mage::getSingleton('adminhtml/session')->getSlideData() )
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getSlideData());
            Mage::getSingleton('adminhtml/session')->setSlideData(null);
        } elseif ( Mage::registry('slide_data') ) {
            $form->setValues(Mage::registry('slide_data')->getData());
        }

        return parent::_prepareForm();
    }
}
<?php
/**
* Landing page model
*
* @author Miroslav Petrov <miroslav.petrov@modacom.com>
*/
class Modacom_Slide_Model_Slide extends Mage_Core_Model_Abstract
{
    /**
    * Define resource model
    */
    protected function _construct()
    {
        $this->_init('modacom_slide/slide');
    }

    public function getImageUrl()
    {
        return Mage::getBaseUrl('media').$this->getImage();
    }
}
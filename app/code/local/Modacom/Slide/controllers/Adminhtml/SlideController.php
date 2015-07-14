<?php
/**
 * @package Modacom_Slide
 * @author Miroslav Petrov <miroslav.petrov@modacom.com>
 */
class Modacom_Slide_Adminhtml_SlideController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Init some data before every action
     * 
     * @return $this Modacom_Slide_Adminhtml_SlideController
     */
    protected function _initAction()
    {
        $this->loadLayout();
        return $this;
    }

    /**
     * Init grid block for listing all slides
     * 
     * @return void
     */
    public function indexAction()
    {
        $this->_initAction()->renderLayout();
    }

    /**
     * Uses edit action without id
     * 
     * @return void
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Create form for editing/creating new slides
     * 
     * @return void
     */
    public function editAction()
    {
        $id     = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('modacom_slide/slide')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('slide_data', $model);

            $this->loadLayout();
            
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('modacom_slide/adminhtml_slide_edit'))
                ->_addLeft($this->getLayout()->createBlock('modacom_slide/adminhtml_slide_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('modacom_slide')->__('Slide page does not exist!'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * Save changes or create new landing pages
     * 
     * @return void
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('modacom_slide/slide');     
            $model->setData($data)->setId($this->getRequest()->getParam('id'));
            try {
                
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('modacom_slide')->__('Slide was successfully saved.'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if (isset($_FILES['image']['name']) && ($_FILES['image']['name'] != '') && ($_FILES['image']['size'] != 0) ) {
                    $uploader = new Varien_File_Uploader('image');
                    $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                    $uploader->setAllowRenameFiles(false);

                    // Set the file upload mode
                    // false -> get the file directly in the specified folder
                    // true -> get the file in folders like /media/a/b/
                    $uploader->setFilesDispersion(false);

                    $path = Mage::getBaseDir('media') . DS . 'slide' . DS;

                    //saved the name in DB
                    $fileName = $_FILES['image']['name'];
                    $result = $uploader->save($path, $fileName);
                    $filepath = 'slide' . DS .$result['file'];
                    /*
                    if (!getimagesize($filepath)) {
                        Mage::throwException($this->__('Disallowed file type.'));
                    }*/
                    $data['image'] = $filepath;
                    $data['image'] = str_replace('\\', '/', $data['image']);
                } elseif (isset($data['image']['delete'])) {
                    $path = Mage::getBaseDir('media') . DS;
                    $result = unlink($path . $data['image']['value']);
                    if ($data['short_height_resize'] && $data['short_width_resize']) {
                        $resizePath = Mage::getBaseDir('media') . DS . 'slide' . DS . $data['short_width_resize'] . 'x' . $data['short_height_resize'] . DS;
                    }
                    $result = unlink($resizePath . str_replace('slide/', '', $data['image']['value']));
                    // $data['image'] = '';
                } else {
                    if (isset($data['image']['value'])) {
                        $data['image'] = $data['image']['value'];
                    }
                }

                $model->setData($data)->setId($this->getRequest()->getParam('id'));
                $model->save();
                
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                Mage::log($e->getMessage(), null, 'miro.log');
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('modacom_slide')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }

    /**
     * Delete landing pages action
     * 
     * @return void
     */
    public function deleteAction() {
        if($this->getRequest()->getParam('id') > 0) {
            try {
                $item = Mage::getModel('modacom_slide/slide')->load($this->getRequest()->getParam('id'));
                $model = Mage::getModel('modacom_slide/slide');
                $model->setId($this->getRequest()->getParam('id'))->delete(); // delete
                     
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Landing Page was successfully deleted.'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Mass delete slides
     * 
     * @return void
     */
    public function massDeleteAction() {
        $slideIds = $this->getRequest()->getParam('slides');
        if (!is_array($slideIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                $model = Mage::getModel('modacom_slide/slide');
                foreach ($slideIds as $slideId) {
                    $model->reset()
                        ->load($slideId)
                        ->delete();
                }
                Mage::getSingleton('adminhtml/session')
                    ->addSuccess(Mage::helper('adminhtml')
                    ->__('%d record(s) have been successfully deleted', count($slideIds)));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Mass change status action for slides
     * 
     * @return void
     */
    public function massStatusAction()
    {
        $slideIds = $this->getRequest()->getParam('slides');
        if (!is_array($slideIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($slideIds as $slideId) {
                    $model = Mage::getSingleton('modacom_slide/slide')
                        ->setSlideId($slideId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->save();
                }
                $this->_getSession()
                    ->addSuccess($this->__('%d record(s) have been successfully updated', count($slideIds)));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
}
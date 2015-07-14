<?php
/**
 * Creates grid with all slides include filters and sorting
 * 
 * @author Miroslav Petrov <miroslav.petrov@modacom.com>
 */
class Modacom_Slide_Block_Adminhtml_Slide_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Set initial data for grid
     * Setting default sorting
     * 
     * @return void 
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('slidesGrid');
        $this->setDefaultSort('slide_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Get slides collection to fill it in grid
     * 
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('modacom_slide/slide')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('slide_id', array(
            'header'    => Mage::helper('modacom_slide')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'slide_id',
        ));

        $this->addColumn('title', array(
                'header'    => Mage::helper('modacom_slide')->__('Title'),
                'align'     =>'left',
                'index'     => 'title',
        ));

        $this->addColumn('url_key', array(
                'header'    => Mage::helper('modacom_slide')->__('Url'),
                'align'     =>'left',
                'index'     => 'url_key',
        ));

        $this->addColumn('status', array(
                'header'    => Mage::helper('modacom_slide')->__('Status'),
                'align'     =>'left',
                'index'     => 'status',
                'type'      => 'options',
                'options'   => array(
                                  0 => Mage::helper('modacom_slide')->__('Disabled'),
                                  1 => Mage::helper('modacom_slide')->__('Enabled'),
                                )
        ));

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('modacom_slide')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('modacom_slide')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
            )
        );
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('slide_id');
        $this->getMassactionBlock()->setFormFieldName('slides');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('modacom_slide')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('modacom_slide')->__('Are you sure?')
        ));

        $statuses = array(
              '' => '',
              '0' => Mage::helper('modacom_slide')->__('Disabled'),
              '1' => Mage::helper('modacom_slide')->__('Enabled'),
        );
        // array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('modacom_slide')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('modacom_slide')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

    /**
     * Generate url for editing item
     * 
     * @param  Modacom_Slide_Model_Slide $item
     * @return string
     */
    public function getRowUrl($item)
    {
        return $this->getUrl('*/*/edit', array('id' => $item->getSlideId()));
    }
}
<?php

namespace Collector\Iframe\Model\ResourceModel\State;

/**
 * Class Collection
 * @package Collector\Iframe\Model\ResourceModel\State
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'status';

    protected function _construct()
    {
        $this->_init(
            'Collector\Iframe\Model\State',
            'Collector\Iframe\Model\ResourceModel\State'
        );
        $this->_map['fields']['entity_id'] = 'main_table.' . $this->_idFieldName;
    }

}

<?php

namespace Collector\Iframe\Model\Config\Source;

class Customertype implements \Magento\Framework\Option\ArrayInterface {
    /**
     * Return array of options as value-label pairs, eg. value => label
     *
     * @return array
     */
    public function toOptionArray(){
        return [
            1 => __('Private Customers'),
            2 => __('Business Customers'),
			3 => __('Private customers & Business customers')
        ];
    }
}
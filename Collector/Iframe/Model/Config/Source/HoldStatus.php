<?php

namespace Collector\Iframe\Model\Config\Source;

class HoldStatus implements \Magento\Framework\Option\ArrayInterface {
    /**
     * Return array of options as value-label pairs, eg. value => label
     *
     * @return array
     */
    public function toOptionArray(){
        return [
            'holded' => __('On Hold'),
            'pending_payment' => __('Pending')
        ];
    }

    public function toArray(){
        return ['holded' => __('On Hold'),'pending_payment' => __('Pending')];
    }
}
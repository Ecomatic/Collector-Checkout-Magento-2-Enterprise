<?php

namespace Collector\Iframe\Model\Config\Source;

class DeniedStatus implements \Magento\Framework\Option\ArrayInterface {
    /**
     * Return array of options as value-label pairs, eg. value => label
     *
     * @return array
     */
    public function toOptionArray(){
        return [
            'canceled' => __('Canceled'),
            'closed' => __('Closed')
        ];
    }

    public function toArray(){
        return ['canceled' => __('Canceled'),'closed' => __('Closed')];
    }
}
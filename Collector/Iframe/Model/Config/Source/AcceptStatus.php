<?php

namespace Collector\Iframe\Model\Config\Source;

class AcceptStatus implements \Magento\Framework\Option\ArrayInterface {
    /**
     * Return array of options as value-label pairs, eg. value => label
     *
     * @return array
     */
    public function toOptionArray(){
        return [
            'processing' => __('Processing'),
            'pending_payment' => __('Pending')
        ];
    }

    public function toArray(){
        return ['processing' => __('Processing'),'pending_payment' => __('Pending')];
    }
}
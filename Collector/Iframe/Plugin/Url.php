<?php
namespace Collector\Iframe\Plugin;

class Url {
    protected $helper;

    public function __construct(\Collector\Iframe\Helper\Data $helper){
        $this->helper = $helper;
    }

    public function afterGetCheckoutUrl($subject,$result){
        if ($this->helper->getEnable()) {
            return '/collectorcheckout';
        }
        return $result;
    }
}
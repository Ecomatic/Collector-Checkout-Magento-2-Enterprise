<?php

namespace Collector\Iframe\Plugin;

class Url
{
    protected $helper;

    public function __construct(\Collector\Iframe\Helper\Data $helper,
                                \Magento\Framework\UrlInterface $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
        $this->helper = $helper;
    }

    public function afterGetCheckoutUrl($subject, $result)
    {
        if ($this->helper->getEnable()) {
            return $this->urlBuilder->getUrl('collectorcheckout');
        }
        return $result;
    }
}

<?php

namespace Collector\Gateways\Exception;

use Magento\Framework\Phrase;
use Magento\Framework\Exception\AbstractAggregateException;

class CouldNotSaveException extends AbstractAggregateException
{
    /**
     * CouldNotSaveException constructor.
     * @param Phrase $phrase
     * @param \Exception|null $cause
     */
    public function __construct(Phrase $phrase, \Exception $cause = null)
    {
        $this->originalPhrase = $phrase;
        parent::__construct($phrase, $cause);
		
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $logger = $objectManager->create('\Psr\Log\LoggerInterface');
        $logger->debug($cause->getMessage());
    }
}
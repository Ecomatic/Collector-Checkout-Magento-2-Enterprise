<?php

namespace Collector\Gateways\Model\Order\Total\Invoice;

use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

class Fee extends AbstractTotal {
	public function collect(\Magento\Sales\Model\Order\Invoice $invoice){
		$invoice->setFeeAmount(0);
		$invoice->setBaseFeeAmount(0);
		$orderFeeAmount = $invoice->getOrder()->getFeeAmount();
		$baseOrderFeeAmount = $invoice->getOrder()->getBaseFeeAmount();
		if ($orderFeeAmount) {
			foreach ($invoice->getOrder()->getInvoiceCollection() as $previousInvoice) {
				if ((double)$previousInvoice->getFeeAmount() && !$previousInvoice->isCanceled()) {
					return $this;
				}
			}
			$invoice->setFeeAmount($orderFeeAmount);
			$invoice->setBaseFeeAmount($baseOrderFeeAmount);
			$invoice->setGrandTotal($invoice->getGrandTotal() + $orderFeeAmount);
			$invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseOrderFeeAmount);
		}
		return $this;
	}
}
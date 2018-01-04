<?php
/**
 * A Magento 2 module named Collector/Iframe
 * Copyright (C) 2017 Collector
 * 
 * This file is part of Collector/Iframe.
 * 
 * Collector/Iframe is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Collector\Iframe\Block\CollectorInvoiceStatus;

class Index extends \Magento\Framework\View\Element\Template
{
    protected $objectManager;

    public function __construct(\Magento\Framework\View\Element\Template\Context $context, array $data = [], \Magento\Framework\ObjectManagerInterface $_objectManager){
        parent::__construct($context, $data);
		$this->objectManager = $_objectManager;
	}
	
	protected function _toHtml(){
		return parent::_toHtml();
	}
}

<?php

namespace Summa\Bundle\BadgeBundle\ImportExport\DataConverter;

use Oro\Bundle\ImportExportBundle\Converter\AbstractTableDataConverter;

class BadgeDataConverter extends AbstractTableDataConverter
{
    /**
     * {@inheritDoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'ProductSku' => 'product:sku',
            'BadgeId' => 'badge:id'
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getBackendHeader()
    {
        return array_values($this->getHeaderConversionRules());
    }
}

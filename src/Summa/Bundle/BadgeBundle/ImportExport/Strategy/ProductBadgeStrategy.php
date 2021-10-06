<?php

namespace Summa\Bundle\BadgeBundle\ImportExport\Strategy;

use Oro\Bundle\ImportExportBundle\Strategy\Import\AbstractImportStrategy;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;

/**
 * Product badge reset strategy
 * It expects the existing prices to be removed from the price list before import
 */
class ProductBadgeStrategy extends ConfigurableAddOrReplaceStrategy{

    public function process($entity){
        $object = $entity;
    }
}

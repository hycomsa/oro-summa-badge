<?php

namespace Summa\Bundle\BadgeBundle\ImportExport\Processor;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Oro\Bundle\ImportExportBundle\Processor\ImportProcessor;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Entity\RelatedItem\RelatedProduct;
use Oro\Bundle\ProductBundle\RelatedItem\AbstractRelatedItemConfigProvider;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Processor for the RelatedProduct entity. Validates that count of product relations does not exceed the limit.
 */
class ProductBadgeImportProcessor extends ImportProcessor
{
    /** @var ManagerRegistry */
    private $registry;

    /** @var TranslatorInterface */
    private $translator;

    /** @var AbstractRelatedItemConfigProvider */
    private $configProvider;

    /** @var ImportStrategyHelper */
    private $importStrategyHelper;

    /** @var AclHelper */
    private $aclHelper;

    /**
     * @param ManagerRegistry $registry
     * @param TranslatorInterface $translator
     * @param AbstractRelatedItemConfigProvider $configProvider
     * @param ImportStrategyHelper $importStrategyHelper
     * @param AclHelper $aclHelper
     */
    public function __construct(
        ManagerRegistry $registry,
        TranslatorInterface $translator,
        AbstractRelatedItemConfigProvider $configProvider,
        ImportStrategyHelper $importStrategyHelper,
        AclHelper $aclHelper
    ) {
        $this->registry = $registry;
        $this->translator = $translator;
        $this->configProvider = $configProvider;
        $this->importStrategyHelper = $importStrategyHelper;
        $this->aclHelper = $aclHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        if (!$this->canBeProcessed($item)) {
            return null;
        }

        $sku = $item['ProductSku'];
        $productId = $this->getProductId($sku);
        if (!$productId) {
            return null;
        }

//
//        $productId = $this->getProductId($sku);
//        if (!$productId) {
//            return null;
//        }
//
//        $relatedSkus = array_unique(explode(',', $item['Related SKUs'] ?? ''));

//        if (!$this->isValidRow($sku, $relatedSkus, $item)) {
//            return null;
//        }

//        $processed = [];
//        foreach ($relatedSkus as $relatedSku) {
//
//
//            if ($object instanceof RelatedProduct && $object->getProduct() && $object->getRelatedItem()) {
//                $processed[] = $object;
//            }
//        }

        $object = parent::process(['ProductSku' => $sku, 'BadgeId' => $item['BadgeId']]);

        return ($object instanceof Product) ? $object : false;
    }

    /**
     * @param string $sku
     * @param array $relatedSkus
     * @param array $item
     *
     * @return bool
     */
//    private function isValidRow(string $sku, array $relatedSkus, array $item): bool
//    {
//        $result = true;
//
//        if (in_array(mb_strtoupper($sku), array_map('mb_strtoupper', $relatedSkus), false)) {
//            $this->addError('oro.product.import.related_sku.self_relation');
//            $result = false;
//        }
//
//        if (in_array('', $relatedSkus, true)) {
//            $this->addError('oro.product.import.related_sku.empty_sku', ['%data%' => json_encode($item)]);
//            $result = false;
//        }
//
//        return $result;
//    }

    /**
     * @param array $item
     *
     * @return bool
     */
    private function canBeProcessed(array $item): bool
    {
        $result = true;

        if (!$this->configProvider->isEnabled()) {
            $result = false;
        }

        if (!isset($item['ProductSku'])) {
            $this->addError('summa.productbadge.import.sku.column_missing');
            $result = false;
        }

        if (!isset($item['BadgeId'])) {
            $this->addError('summa.productbadge.import.badge.column_missing');
            $result = false;
        }

        return $result;
    }

    /**
     * @param string $sku
     * @return int|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function getProductId(string $sku): ?int
    {
        $qb = $this->getRepository(Product::class)->getProductIdBySkuQueryBuilder($sku);
        $product = $this->aclHelper->apply($qb)
            ->getOneOrNullResult();

        if (!isset($product['id'])) {
            $this->addError('summa.productbadge.import.sku.product_by_sku.not_found');
            return null;
        }

        return $product['id'];
    }

    /**
     * @param string $className
     * @return ObjectRepository
     */
    private function getRepository(string $className): ObjectRepository
    {
        return $this->registry->getManagerForClass($className)->getRepository($className);
    }

    /**
     * @param string $error
     * @param array $parameters
     */
    private function addError(string $error, array $parameters = []): void
    {
        $this->context->incrementErrorEntriesCount();

        $this->importStrategyHelper->addValidationErrors(
            [$this->translator->trans($error, $parameters, 'validators')],
            $this->context
        );
    }
}

<?php

namespace  Summa\Bundle\BadgeBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Event\PreBuild;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;
use Oro\Bundle\ProductBundle\DataGrid\DataGridThemeHelper;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Entity\Repository\ProductRepository;
use Oro\Bundle\SearchBundle\Datagrid\Event\SearchResultAfter;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Summa\Bundle\BadgeBundle\Layout\DataProvider\ProductBadgeProvider;

/**
 * Add badges on product grid results
 */
class FrontendProductBadgeDatagridListener
{
    /**
     * @internal
     */
    const COLUMN_PRODUCT_BADGES = 'badges';

    /**
     * @var DataGridThemeHelper
     */
    protected $themeHelper;

    /**
     * @var ProductBadgeProvider
     */
    protected $productBadgeProvider;

    /** DoctrineHelper */
    private $doctrineHelper;

    /**
     * @param DataGridThemeHelper $themeHelper
     * @param ProductBadgeProvider $productBadgeProvider
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(
        DataGridThemeHelper $themeHelper,
        ProductBadgeProvider $productBadgeProvider,
        DoctrineHelper $doctrineHelper
    ) {
        $this->themeHelper = $themeHelper;
        $this->productBadgeProvider = $productBadgeProvider;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param PreBuild $event
     */
    public function onPreBuild(PreBuild $event)
    {
        $config = $event->getConfig();

        $config->offsetAddToArrayByPath(
            '[properties]',
            [
                self::COLUMN_PRODUCT_BADGES => [
                    'type' => 'field',
                    'frontend_type' => PropertyInterface::TYPE_ROW_ARRAY,
                ],
            ]
        );
    }

    /**
     * @param SearchResultAfter $event
     */
    public function onResultAfter(SearchResultAfter $event)
    {
        /** @var ResultRecord[] $records */
        $records = $event->getRecords();

        $this->addProductBadges($event, $records);
    }

    /**
     * @param ResultRecord[] $records
     */
    protected function addProductBadges(SearchResultAfter $event, array $records)
    {
        $gridName = $event->getDatagrid()->getName();
        $theme    = $this->themeHelper->getTheme($gridName);

        if($theme == DataGridThemeHelper::VIEW_LIST){
            return ;
        }

        $productsWithBadges = $this->prepareDataForBadgesCollection($records);
        if (!$productsWithBadges) {
            return;
        }

        foreach ($records as $record) {
            $productId = $record->getValue('id');
            if (array_key_exists($productId, $productsWithBadges)) {
                $record->addData([self::COLUMN_PRODUCT_BADGES => $productsWithBadges[$productId]]);
            }
        }
    }

    /**
     * @param ResultRecord[] $records
     *
     * @return Product[]
     */
    protected function prepareDataForBadgesCollection(array $records)
    {
        $products = $this->getProductsEntities($records);

        return $this->productBadgeProvider->getBadgesForProducts($products);
    }

    /**
     * @param ResultRecord[] $records
     *
     * @return Product[]
     */
    protected function getProductsEntities(array $records)
    {
        $products = [];

        /** @var ResultRecord[] $records */
        foreach ($records as $record) {
            $products[] = $record->getValue('id');
        }

        /** @var ProductRepository $productRepository */
        $productRepository = $this->doctrineHelper->getEntityRepositoryForClass(Product::class);

        return $productRepository->findBy(['id' => $products]);
    }
}

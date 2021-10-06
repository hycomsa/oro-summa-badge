<?php

namespace Summa\Bundle\BadgeBundle\Layout\DataProvider;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\ProductBundle\Entity\Product;
use Summa\Bundle\BadgeBundle\Entity\Badge;

class ProductBadgeProvider
{
    /** @var EntityManager */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(
        EntityManager $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Product $product
     * @return array
     */
    public function getActiveBadges(Product $product)
    {
        $badges = [];
        if(!$product->getBadges()->isEmpty()){
            $badges = $this->entityManager
                ->getRepository(Badge::class)
                ->getActiveBadges($product);
        }
        return $badges;
    }

    /**
     * @param array $products
     * @return array
     */
    public function getBadgesForProducts(array $products)
    {
        $groupedBadges = [];
        foreach ($products as $product) {
            $groupedBadges[$product->getId()] = $this->getActiveBadges($product);
        }

        return $groupedBadges;
    }
}

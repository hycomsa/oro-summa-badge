<?php

namespace Summa\Bundle\BadgeBundle\Entity;

use Oro\Bundle\ProductBundle\Entity\Product;
use Summa\Bundle\BadgeBundle\Model\ExtendProductBadge;

class ProductBadge extends ExtendProductBadge
{
    /**
     * @var Product
     */
    private $product;

    /**
     * @var Badge
     */
    private $badge;

    /**
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * @param Product $product
     */
    public function setProduct(Product $product): void
    {
        $this->product = $product;
    }

    /**
     * @return Badge
     */
    public function getBadge(): Badge
    {
        return $this->badge;
    }

    /**
     * @param Badge $badge
     */
    public function setBadge(Badge $badge): void
    {
        $this->badge = $badge;
    }

}
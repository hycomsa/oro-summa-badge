<?php

namespace Summa\Bundle\BadgeBundle\ImportExport\TemplateFixture;

use Oro\Bundle\ImportExportBundle\TemplateFixture\AbstractTemplateRepository;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateFixtureInterface;
use Oro\Bundle\ProductBundle\Entity\Product;
use Summa\Bundle\BadgeBundle\Entity\Badge;
use Summa\Bundle\BadgeBundle\Entity\ProductBadge;

class BadgeProductFixture extends AbstractTemplateRepository implements TemplateFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return 'Oro/Bundle/ProductBundle/Entity/Product';
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->getEntityData('Example Product Badge');
    }

    /**
     * @param string  $key
     */
    protected function createEntity($key)
    {
        return new Product();
    }

    /**
     * @param string  $key
     * @param Product $entity
     */
    public function fillEntityData($key, $entity)
    {
//        parent::fillEntityData($key, $entity);
        /** @var Product $product */
        //$product = new Product();
        $entity->setSku('product-1');

        /** @var Badge $badge */
        $badge   = new Badge();
        $badge->setId(1);
        $badge->setName('Badge-1');
        $badge->setPosition('top-left');

        //$entity->setProduct($product);
        $entity->addBadges($badge);
    }
}

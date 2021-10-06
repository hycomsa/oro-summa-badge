<?php

namespace Summa\Bundle\BadgeBundle\ImportExport\Normalizer;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\ImportExportBundle\Event\Events;
use Oro\Bundle\ImportExportBundle\Exception\UnexpectedValueException;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\DenormalizerInterface;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\ConfigurableEntityNormalizer;
use Oro\Bundle\ProductBundle\Entity\Product;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Summa\Bundle\BadgeBundle\Entity\BadgeProduct;
use Summa\Bundle\BadgeBundle\Entity\Badge;

class BadgeProductNormalizer extends ConfigurableEntityNormalizer
{

    /** @var Registry */
    protected $registry;

    /**
     * @var string
     */
    protected $badgeProductClass;

    /**
     * @param string $productClass
     */
    public function setBadgeProductClass($badgeProductClass)
    {
        $this->badgeProductClass = $badgeProductClass;
    }

    /**
     * @param Registry $registry
     */
    public function setRegistry(Registry $registry): void
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data= array();
        $data['product'] = [
            'sku' => $object->getSku()
        ];
        $data['badge'] = [
            'id' => $object->getBadges()->first()->getId()
        ];

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if (!isset($data['product']['sku'], $data['badge']['id'])) {
            return null;
        }

        $product = $this->registry->getManager()
            ->getRepository(Product::class)
            ->findOneBy(['sku' => $data['product']['sku']]);

        $badge = $this->registry->getManager()
            ->getRepository(Badge::class)
            ->findOneBy([ 'id' => $data['badge']['id']]);

        $product->addBadges($badge);

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = [])
    {
//        return is_a($data, $this->badgeProductClass);
        return $data instanceof Product;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = [])
    {
        return is_a($type, $this->badgeProductClass, true);
    }
}

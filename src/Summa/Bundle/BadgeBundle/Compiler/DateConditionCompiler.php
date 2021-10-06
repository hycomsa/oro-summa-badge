<?php

namespace Summa\Bundle\BadgeBundle\Compiler;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\ProductBundle\Entity\Product;
use Summa\Bundle\BadgeBundle\Entity\Badge;

/**
 * Compile product assignment rule to Query builder with all applied restrictions.
 */
class DateConditionCompiler extends AbstractRuleCompiler
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var array
     */
    protected $fieldsOrder = [
        'product_id'
    ];

    /**
     * @param Badge $badge
     * @return QueryBuilder|mixed|null
     * @throws \Exception
     */
    public function compile(Badge $badge)
    {
        if (!$badge->getApplyForNDays()){
            return null;
        }

        $cacheKey = 'bg_dc_' . $badge->getId();
        $qb = $this->cache->fetch($cacheKey);
        if (!$qb) {
            $qb = $this->compileQueryBuilder($badge);
            $this->cache->save($cacheKey, $qb);
        }

        return $qb;
    }

    /**
     * @param Badge $badge
     * @return QueryBuilder
     * @throws \Exception
     */
    public function compileQueryBuilder(Badge $badge): QueryBuilder
    {
        $qb = $this->createQueryBuilder();

        $aliases = $qb->getRootAliases();
        $rootAlias = reset($aliases);

        $this->modifySelectPart($qb, $badge, $rootAlias);
        $this->applyForNDays($qb, $badge);
        $qb->addGroupBy($rootAlias . '.id');

        return $qb;
    }

    /**
     * @return mixed
     */
    protected function createQueryBuilder()
    {
        $qb = $this->registry->getManagerForClass(Product::class)
            ->getRepository(Product::class)
            ->createQueryBuilder('product');

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderedFields()
    {
        return $this->fieldsOrder;
    }

    /**
     * @param QueryBuilder $qb
     * @param Badge $badge
     * @param $rootAlias
     */
    protected function modifySelectPart(QueryBuilder $qb, Badge $badge, $rootAlias)
    {
        $this->addSelectInOrder(
            $qb,
            [
                'product_id' => $rootAlias . '.id'
            ]
        );
    }

    /**
     * @param QueryBuilder $qb
     * @param Badge $badge
     * @throws \Exception
     */
    protected function applyForNDays(QueryBuilder $qb, Badge $badge)
    {
        if($badge->getApplyForNDays()){
            $aliases = $qb->getRootAliases();
            $rootAlias = reset($aliases);

            $dateEnd   = new \DateTime('now', new \DateTimeZone('UTC'));
            $dateStart = new \DateTime('-'.$badge->getApplyForNDays().' days', new \DateTimeZone('UTC'));
            $qb->where(
                    $qb->expr()->between($rootAlias.'.createdAt', ':dateStart', ':dateEnd')
                )->setParameter('dateStart', $dateStart, Types::DATETIME_MUTABLE)
                 ->setParameter('dateEnd', $dateEnd, Types::DATETIME_MUTABLE);
        }
    }

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function setRegistry(ManagerRegistry $managerRegistry): void
    {
        $this->registry = $managerRegistry;
   }
}

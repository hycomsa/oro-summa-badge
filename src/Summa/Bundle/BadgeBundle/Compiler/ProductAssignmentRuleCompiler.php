<?php

namespace Summa\Bundle\BadgeBundle\Compiler;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\ProductBundle\Entity\Product;
use Summa\Bundle\BadgeBundle\Entity\Badge;

/**
 * Compile product assignment rule to Query builder with all applied restrictions.
 */
class ProductAssignmentRuleCompiler extends AbstractRuleCompiler
{
    /**
     * @var array
     */
    protected $fieldsOrder = [
        'product_id'
    ];

    /**
     * @param Badge $badge
     * @param array $products
     * @return QueryBuilder|mixed|null
     * @throws \Exception
     */
    public function compile(Badge $badge, array $products = [])
    {
        if (!$badge->getProductAssignmentRule()) {
            return null;
        }

        $cacheKey = 'bg_' . $badge->getId();
        $qb = $this->cache->fetch($cacheKey);
        if (!$qb) {
            $qb = $this->compileQueryBuilder($badge);

            $this->cache->save($cacheKey, $qb);
        }

        $this->restrictByGivenProduct($qb, $products);

        return $qb;
    }

    /**
     * @param Badge $badge
     * @return QueryBuilder
     * @throws \Exception
     */
    public function compileQueryBuilder(Badge $badge): QueryBuilder
    {
        $qb = $this->createQueryBuilder($badge);

        $aliases = $qb->getRootAliases();
        $rootAlias = reset($aliases);

        $this->modifySelectPart($qb, $rootAlias);

        $this->applyRuleConditions($qb, $badge);
        $this->applyForNDays($qb, $badge);

        $qb->addGroupBy($rootAlias . '.id');

        return $qb;
    }

    /**
     * @param Badge $badge
     * @return QueryBuilder
     */
    protected function createQueryBuilder(Badge $badge)
    {
        $rule = $this->getProcessedAssignmentRule($badge);
        $node = $this->expressionParser->parse($rule);
        $source = $this->nodeConverter->convert($node);

        return $this->queryConverter->convert($source);
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
     * @param $rootAlias
     */
    protected function modifySelectPart(QueryBuilder $qb, $rootAlias)
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
     */
    protected function applyRuleConditions(QueryBuilder $qb, Badge $badge)
    {
        $params = [];
        $rule = $this->getProcessedAssignmentRule($badge);
        $qb->andWhere(
            $this->expressionBuilder->convert(
                $this->expressionParser->parse($rule),
                $qb->expr(),
                $params,
                $this->queryConverter->getTableAliasByColumn()
            )
        );
        $this->applyParameters($qb, $params);
    }

    /**
     * @param QueryBuilder $qb
     * @param array|Product[] $products
     */
    protected function restrictByGivenProduct(QueryBuilder $qb, array $products = [])
    {
        if ($products) {
            $aliases = $qb->getRootAliases();
            $rootAlias = reset($aliases);
            $qb->andWhere($qb->expr()->in($rootAlias, ':products'))
                ->setParameter('products', $products);
        }
    }

    /**
     * @param Badge $badge
     * @return string
     */
    protected function getProcessedAssignmentRule(Badge $badge)
    {
        return $this->expressionPreprocessor->process($badge->getProductAssignmentRule());
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

            $qb->andWhere(
                    $qb->expr()->between($rootAlias.'.createdAt', ':dateStart', ':dateEnd')
                )->setParameter('dateStart', $dateStart, Types::DATETIME_MUTABLE)
                 ->setParameter('dateEnd', $dateEnd, Types::DATETIME_MUTABLE);
        }
    }

}

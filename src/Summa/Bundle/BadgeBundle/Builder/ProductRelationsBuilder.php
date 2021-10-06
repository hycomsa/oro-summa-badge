<?php

namespace Summa\Bundle\BadgeBundle\Builder;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Types\Types;
use Summa\Bundle\BadgeBundle\Compiler\DateConditionCompiler;
use Summa\Bundle\BadgeBundle\Entity\Badge;
use Summa\Bundle\BadgeBundle\Compiler\ProductAssignmentRuleCompiler;


/**
 * Builder for relations between badge and products
 */
class ProductRelationsBuilder
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /** @var ProductAssignmentRuleCompiler */
    private $productAssignmentRuleCompiler;

    /** @var DateConditionCompiler */
    private $dateConditionCompiler;

    /**
     * @param ManagerRegistry $registry
     * @param ProductAssignmentRuleCompiler $productAssignmentRuleCompiler
     */
    public function __construct(
        ManagerRegistry $registry,
        ProductAssignmentRuleCompiler $productAssignmentRuleCompiler,
        DateConditionCompiler $dateConditionCompiler
    ) {
        $this->registry = $registry;
        $this->productAssignmentRuleCompiler = $productAssignmentRuleCompiler;
        $this->dateConditionCompiler = $dateConditionCompiler;
    }

    /**
     * @param Badge $badge
     * @throws \Exception
     */
    public function builder(Badge $badge){
        $this->clearProductRelated($badge);
        $this->addProductRelated($badge);

    }

    /**
     * @param Badge $badge
     */
    private function clearProductRelated(Badge $badge)
    {
        try{
            $params = ['badgeId'  => $badge->getId()];
            $types  = ['badgeId'  => Types::INTEGER];

            $sql='DELETE FROM oro_rel_product_badge_badges
                WHERE badge_id = :badgeId ;';

            $this->registry->getConnection()->fetchAll($sql, $params, $types);
            return;
        }catch (\Exception $e){
            // Todo: log exception
        }
    }

    /**
     * @param Badge $badge
     * @throws \Exception
     */
    private function addProductRelated(Badge $badge)
    {
        // TODO: solo insertar lo que sea necesario
        $productMatch = $this->getProductMatches($badge);
        if($productMatch){
            try{
                foreach ($productMatch as $product){
                    $sql = 'INSERT INTO oro_rel_product_badge_badges (product_id, badge_id)
                    VALUES (:productId, :badgeId);';
                    $params = ['badgeId'  => $badge->getId(), 'productId' => $product['product_id']];

                    $types = ['productId' => Types::INTEGER, 'badgeId' => Types::INTEGER ];

                    $this->registry->getConnection()->executeQuery($sql, $params, $types);
                }
                return;
            }catch (\Exception $e){
                // Todo: log exception
            }
        }
    }

    /**
     * @param Badge $badge
     * @return bool
     * @throws \Exception
     */
    public function needRebuild(Badge $badge): bool
    {
        $productMatch = $this->getProductMatches($badge);

        $params = ['badgeId'  => $badge->getId()];
        $types  = ['badgeId'  => Types::INTEGER];
        $sql='SELECT product_id FROM oro_rel_product_badge_badges
                WHERE badge_id = :badgeId ;';

        $productRelated = $this->registry->getConnection()->fetchAll($sql, $params, $types);
        try{
            return count(array_diff(array_column($productMatch,'product_id'), array_column($productRelated,'product_id'))) > 0;
        }catch (\Exception $e){
            return true;
        }
    }

    /**
     * @param Badge $badge
     * @return int|mixed|string|void|null
     * @throws \Exception
     */
    private function getProductMatches(Badge $badge)
    {
        if(!$badge->getProductAssignmentRule() && $badge->getApplyForNDays()){
            $qb = $this->dateConditionCompiler->compile($badge);
            return ($qb) ? $qb->getQuery()->execute() : [];
        }

        if($badge->getProductAssignmentRule()){
            $qb = $this->productAssignmentRuleCompiler->compile($badge);
            return ($qb) ? $qb->getQuery()->execute() : [];
        }
    }
}

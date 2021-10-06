<?php

namespace Summa\Bundle\BadgeBundle\Entity\EntityListener;

use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Summa\Bundle\BadgeBundle\Builder\ProductRelationsBuilder;
use Oro\Bundle\PlatformBundle\EventListener\OptionalListenerInterface;
use Oro\Bundle\PlatformBundle\EventListener\OptionalListenerTrait;
use Summa\Bundle\BadgeBundle\Entity\Badge;

class BadgeEntityListener implements OptionalListenerInterface
{
    use OptionalListenerTrait;

    /** @var ProductRelationsBuilder */
    private $builder;

    /**
     * @param ProductRelationsBuilder $builder
     */
    public function __construct(
        ProductRelationsBuilder $builder
    ) {
        $this->builder = $builder;
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $unitOfWork = $args->getEntityManager()->getUnitOfWork();
        foreach ($unitOfWork->getIdentityMap() as $entities) {
            foreach ($entities as $entity){
                if ($entity instanceof Badge) {
                    /** @var Badge $entity */
                    if($entity->getApplyForNDays() || $entity->getProductAssignmentRule()){
                        if($this->isProductAssignmentRuleChanged($unitOfWork,$entity) ||
                            $this->isApplyForNDaysChanged($unitOfWork,$entity))
                        {
                            $this->triggerBadgeRelation($entity);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param Badge $badge
     * @throws \Exception
     */
    protected function triggerBadgeRelation(Badge $badge)
    {
        if (!$badge->isActive()) {
            return;
        }

        if ($this->builder->needRebuild($badge)){
            $this->builder->builder($badge);
        }
    }

    /**
     * @param UnitOfWork $unitOfWork
     * @param Badge $entity
     *
     * @return bool
     */
    private function isProductAssignmentRuleChanged(UnitOfWork $unitOfWork, Badge $entity)
    {
        $changeSet = $unitOfWork->getEntityChangeSet($entity);

        return isset($changeSet['productAssignmentRule']) ? true : false;
    }

    /**
     * @param UnitOfWork $unitOfWork
     * @param Badge $entity
     * @return bool
     */
    private function isApplyForNDaysChanged(UnitOfWork $unitOfWork, Badge $entity)
    {
        $changeSet = $unitOfWork->getEntityChangeSet($entity);

        return isset($changeSet['applyForNDays']) ? true : false;
    }
}

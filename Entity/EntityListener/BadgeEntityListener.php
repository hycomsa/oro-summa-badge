<?php

namespace Summa\Bundle\BadgeBundle\Entity\EntityListener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Summa\Bundle\BadgeBundle\Builder\ProductRelationsBuilder;
use Summa\Bundle\BadgeBundle\Compiler\ProductAssignmentRuleCompiler;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Oro\Bundle\PlatformBundle\EventListener\OptionalListenerInterface;
use Oro\Bundle\PlatformBundle\EventListener\OptionalListenerTrait;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Summa\Bundle\BadgeBundle\Async\Topics;
use Summa\Bundle\BadgeBundle\Entity\Badge;

class BadgeEntityListener implements OptionalListenerInterface
{
    use OptionalListenerTrait;

    /** @var FlashBagInterface */
    protected $flashBag;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var MessageProducerInterface */
    protected $messageProducer;

    // TODO: remove only for test
    /** @var ProductAssignmentRuleCompiler */
    protected $productAssignmentRuleCompiler;

    /** @var ManagerRegistry */
    private $registry;

    private $builder;

    /**
     * @param FlashBagInterface $flashBag
     * @param TranslatorInterface $translator
     * @param MessageProducerInterface $messageProducer
     * @param ProductAssignmentRuleCompiler $productAssignmentRuleCompiler
     * @param ManagerRegistry $registry
     */
    public function __construct(
        FlashBagInterface $flashBag,
        TranslatorInterface $translator,
        MessageProducerInterface $messageProducer,
        ProductAssignmentRuleCompiler $productAssignmentRuleCompiler,
        ManagerRegistry $registry,
        ProductRelationsBuilder $builder
    ) {
        $this->flashBag = $flashBag;
        $this->translator = $translator;
        $this->messageProducer = $messageProducer;
        $this->productAssignmentRuleCompiler = $productAssignmentRuleCompiler;
        $this->registry = $registry;
        $this->builder = $builder;
    }

    /**
     * @param OnFlushEventArgs $event
     */
    public function onFlush(OnFlushEventArgs $event)
    {
        $unitOfWork = $event->getEntityManager()->getUnitOfWork();

        foreach ((array) $unitOfWork->getScheduledEntityUpdates() as $entity) {
            $this->detectedChanges($unitOfWork, $entity);
        }

        foreach ((array) $unitOfWork->getScheduledEntityInsertions() as $entity) {
            //$this->detectedChanges($unitOfWork, $entity);
            //Guarda que no existe entity->getId();
        }
    }

    /**
     * @param UnitOfWork $unitOfWork
     * @param $entity
     * @throws \Oro\Component\MessageQueue\Transport\Exception\Exception
     */
    protected function detectedChanges(UnitOfWork $unitOfWork, $entity)
    {
        /** @var Badge $entity */
        if ($entity instanceof Badge) {
            if($this->isProductAssignmentRuleChanged($unitOfWork,$entity) ||
                $this->isApplyForNDaysChanged($unitOfWork,$entity))
            {
                $this->triggerBadgeRelation($entity);
            }
        }
    }

    protected function triggerBadgeRelation(Badge $badge)
    {
        if (!$badge->isActive()) {
            return;
        }

        if ($this->builder->needRebuild($badge)){
            $this->builder->builder($badge);
        }



//        $this->flashBag->add(
//            'success',
//            $this->translator->trans('summa.productbadge.listener.product_assignment_rule.message')
//        );

        //        $this->messageProducer->send(
//            Topics::RESOLVE_BADGE_ASSIGNED_PRODUCTS,
//            [
//                'badge_id' => $badge->getId()
//            ]
//        );

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

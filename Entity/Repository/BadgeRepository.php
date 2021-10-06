<?php

namespace Summa\Bundle\BadgeBundle\Entity\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\ProductBundle\Entity\Product;
use Summa\Bundle\BadgeBundle\Entity\Badge;

class BadgeRepository extends EntityRepository
{

    /**
     * @param array $badgeIds
     * @return QueryBuilder
     */
    private function getImagesBadgeQueryBuilder(array $badgeIds): QueryBuilder
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('af as image, IDENTITY(af.parentEntityId) as badge_id')
            ->from('OroAttachmentBundle:File', 'af');

        $qb->where($qb->expr()->in('af.parentEntityId', ':badges'))
            ->setParameter('badges', $badgeIds)
            ->getQuery()->execute();
        return $qb;
    }

    /**
     * @param $badgeId
     * @return int|mixed|string
     */
    public function getImageFileByBadge($badgeId)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('imageFile')
            ->from(File::class, 'imageFile')
            ->join(
                Badge::class,
                'cu',
                Expr\Join::WITH,
                'imageFile.id = cu.image'
            );

        $qb->where('cu.id = :badge_id')
            ->setParameter('badge_id', $badgeId);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $productId
     * @return int|mixed|string
     */
    public function getBadgesByProductId($productId)
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('imageFile')
            ->from(File::class, 'imageFile')
            ->join(
                Badge::class,
                'cu',
                Expr\Join::WITH,
                'imageFile.id = cu.image'
            );

        $qb->where('cu.id = :badge_id')
            ->setParameter('badge_id', $productId);

        return $qb->getQuery()->execute();
    }

    /**
     * @param Product $product
     * @return int|mixed|string
     * @throws \Exception
     */
    public function getActiveBadges(Product $product)
    {
        $badges = $product->getBadges();

        $qb = $this->createQueryBuilder('badge');
        $qb->leftJoin(
            'badge.schedules',
            'schedule',
            Expr\Join::WITH,
            '(schedule.badge = badge) AND ((schedule.activeAt IS NOT NULL AND schedule.deactivateAt IS NOT NULL AND :now BETWEEN schedule.activeAt AND schedule.deactivateAt) OR (schedule.activeAt IS NULL AND :now < schedule.deactivateAt) OR (schedule.deactivateAt IS NULL AND :now > schedule.activeAt))')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->in('badge.id', ':badges'),
                    $qb->expr()->eq('badge.active', ':active'),
                    $qb->expr()->neq('badge.containSchedule',':containSchedule')
                )
            )
            ->orWhere(
                $qb->expr()->andX(
                    $qb->expr()->in('badge.id', ':badges'),
                    $qb->expr()->eq('badge.active', ':active'),
                    $qb->expr()->eq('badge.containSchedule',':containSchedule'),
                    $qb->expr()->isNotNull('schedule')
                )
            )
            ->setParameter('badges', $badges)
            ->setParameter('active', true)
            ->setParameter('containSchedule', true)
            ->setParameter('now', new \DateTime('now', new \DateTimeZone('UTC')));

        return $qb->getQuery()->execute();
    }

    /**
     * Return Badges for process with cron
     *
     * @return ArrayCollection|Badge[]
     */
    public function getActiveBadgesCroneable()
    {
        $qb = $this->createQueryBuilder('badge');
        $qb->where('badge.active = :active')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isNotNull('badge.productAssignmentRule'),
                    $qb->expr()->isNotNull('badge.applyForNDays')
                )
            )
            ->setParameter('active', true);

        return $qb->getQuery()->execute();
    }
}
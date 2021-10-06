<?php

namespace Summa\Bundle\BadgeBundle\DataGrid\EventListener;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\DataGridBundle\Event\OrmResultAfter;
use Oro\Bundle\DataGridBundle\Provider\SelectedFields\SelectedFieldsProviderInterface;
use Summa\Bundle\BadgeBundle\Entity\Badge;
use Summa\Bundle\BadgeBundle\Entity\Repository\BadgeRepository;


/**
 * Updates configuration of products grid and add product image on it
 */
class ProductBadgeGridEventListener
{
    /** @var EntityManager */
    private $entityManager;

    /** @var SelectedFieldsProviderInterface */
    private $selectedFieldsProvider;

    /**
     * @param EntityManager $entityManager
     * @param SelectedFieldsProviderInterface $selectedFieldsProvider
     */
    public function __construct(
        EntityManager $entityManager,
        SelectedFieldsProviderInterface $selectedFieldsProvider
    )
    {
        $this->entityManager            = $entityManager;
        $this->selectedFieldsProvider   = $selectedFieldsProvider;
    }

    /**
     * @param OrmResultAfter $event
     */
    public function onResultAfter(OrmResultAfter $event): void
    {
        $datagrid = $event->getDatagrid();

        $selectedFields = $this->selectedFieldsProvider
            ->getSelectedFields($datagrid->getConfig(), $datagrid->getParameters());

        if (!in_array('image', $selectedFields, true)) {
            return;
        }

        /** @var ResultRecordInterface[] $records */
        $records = $event->getRecords();
        if (!$records) {
            return;
        }

        $ids = [];
        foreach ($records as $record) {
            $ids[] = $record->getValue('id');
        }

        /** @var BadgeRepository $badgeRepository */
        $badgeRepository = $this->entityManager->getRepository(Badge::class);

        $images = [];
        foreach ($ids as $id) {
            $images[$id] = $badgeRepository->getImageFileByBadge($id);
        }

        foreach ($records as $record) {
            $id = $record->getValue('id');

            if (isset($images[$id]) && !empty($images[$id]) ) {
                $record->setValue('badgeImage', $images[$id][0]);
            }
        }

        $event->setRecords($records);
    }
}

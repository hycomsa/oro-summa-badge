<?php

namespace Summa\Bundle\BadgeBundle\Command;

use Doctrine\Common\Persistence\ManagerRegistry;
use Summa\Bundle\BadgeBundle\Builder\ProductRelationsBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Summa\Bundle\BadgeBundle\Entity\Badge;

/**
 * Prepares and activates combined price list by schedule
 */
class BadgeScheduleCommand extends Command implements CronCommandInterface
{
    /** @var string */
    protected static $defaultName = 'oro:cron:badge:schedule';

    /** @var ManagerRegistry */
    private $registry;

    /** @var ProductRelationsBuilder */
    private $builder;

    /**
     * @param ManagerRegistry $registry
     * @param ProductRelationsBuilder $productRelationsBuilder
     */
    public function __construct(
        ManagerRegistry $registry,
        ProductRelationsBuilder $productRelationsBuilder
    ) {
        $this->registry = $registry;
        $this->builder = $productRelationsBuilder;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Prepare and activate combined price list by schedule');
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        //Todo: implement flag enable or disable.
        //$offsetHours = $this->configManager->get('oro_pricing.offset_of_processing_cpl_prices');

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $badgesToProcess = $this->registry
            ->getManagerForClass(Badge::class)
            ->getRepository(Badge::class)
            ->getActiveBadgesCroneable();

        foreach ($badgesToProcess as $badge){
            if ($this->builder->needRebuild($badge)){
                $this->builder->builder($badge);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultDefinition()
    {
        return '0 * * * *';
    }
}

<?php

namespace  Summa\Bundle\BadgeBundle\EventListener;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;

class BadgeDatagridListener
{
    /**
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $params = $event->getDatagrid()->getParameters();
        $params->set('now', new \DateTime('now', new \DateTimeZone('UTC')));
    }
}

<?php

namespace Summa\Bundle\BadgeBundle\Migrations\Data\ORM;

use Oro\Bundle\EntityExtendBundle\Migration\Fixture\AbstractEnumFixture;
use Summa\Bundle\BadgeBundle\Entity\Badge;

class LoadProductBadgePositions extends AbstractEnumFixture
{
    const TOP_LEFT      = 'top-left';
    const TOP_RIGHT     = 'top-right';
    const MIDDLE_LEFT   = 'middle-left';
    const MIDDLE_RIGHT  = 'middle-right';
    const BOTTOM_LEFT   = 'bottom-left';
    const BOTTOM_RIGHT  = 'bottom-right';

    /** @var array */
    protected static $data = [
        self::TOP_LEFT     => 'Top Left',
        self::TOP_RIGHT    => 'Top Right',
        self::MIDDLE_LEFT  => 'Middle Left',
        self::MIDDLE_RIGHT => 'Middle Right',
        self::BOTTOM_LEFT  => 'Bottom Left',
        self::BOTTOM_RIGHT => 'Bottom Right'
    ];

    /**
     * {@inheritdoc}
     */
    protected function getData()
    {
        return self::$data;
    }

    /**
     * Returns array of data keys
     *
     * @return array
     */
    public static function getDataKeys()
    {
        return array_keys(self::$data);
    }

    /**
     * {@inheritdoc}
     */
    protected function getEnumCode()
    {
        return Badge::SUMMA_BADGE_POSITION;
    }
}

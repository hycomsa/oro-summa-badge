<?php

namespace Summa\Bundle\BadgeBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class ProductBadgeTwigExtension extends AbstractExtension
{

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('website_config_value', [$this, 'getConfigValue'])
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'badge_extension';
    }

}
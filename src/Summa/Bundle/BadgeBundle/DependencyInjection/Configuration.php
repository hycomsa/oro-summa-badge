<?php

namespace Summa\Bundle\BadgeBundle\DependencyInjection;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('summa_badge');

        SettingsBuilder::append(
            $rootNode,
            [
                'disable_oro_default_badge' => ['type' => 'boolean', 'value' => true]
            ]
        );
        return $treeBuilder;
    }
}
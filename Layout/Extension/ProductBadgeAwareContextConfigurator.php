<?php

namespace Summa\Bundle\BadgeBundle\Layout\Extension;

use Oro\Bundle\CheckoutBundle\Entity\CheckoutInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Component\Layout\ContextConfiguratorInterface;
use Oro\Component\Layout\ContextInterface;

/**
 * Sets "newCheckoutPageLayout" to context if the corresponding option is enabled in the system configuration.
 */
class ProductBadgeAwareContextConfigurator implements ContextConfiguratorInterface
{
    /** @var ConfigManager */
    private $configManager;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * {@inheritdoc}
     */
    public function configureContext(ContextInterface $context): void
    {
        $context->getResolver()->setDefault('disableOroDefaultBadge', false);
        $context->set(
            'disableOroDefaultBadge',
            $this->configManager->get('summa_product_badge.disable_oro_default_badge')
        );
    }
}
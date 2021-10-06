<?php

namespace Summa\Bundle\BadgeBundle\ImportExport\Configuration;

use Symfony\Contracts\Translation\TranslatorInterface;
use Oro\Bundle\ImportExportBundle\Configuration\ImportExportConfiguration;
use Oro\Bundle\ImportExportBundle\Configuration\ImportExportConfigurationInterface;
use Oro\Bundle\ImportExportBundle\Configuration\ImportExportConfigurationProviderInterface;
use Oro\Bundle\ProductBundle\Entity\Product;

class BadgeProductImportConfigurationProvider implements ImportExportConfigurationProviderInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \InvalidArgumentException
     */
    public function get(): ImportExportConfigurationInterface
    {
        return new ImportExportConfiguration([
            ImportExportConfiguration::FIELD_ENTITY_CLASS => Product::class,
            ImportExportConfiguration::FIELD_EXPORT_TEMPLATE_PROCESSOR_ALIAS => 'summa_product_badge', //No funciona
            ImportExportConfiguration::FIELD_IMPORT_JOB_NAME => 'summa_product_badge_entity_import_from_csv',
            ImportExportConfiguration::FIELD_IMPORT_PROCESSOR_ALIAS => 'summa_product_badge.add_or_replace',
            ImportExportConfiguration::FIELD_IMPORT_STRATEGY_TOOLTIP =>
                $this->translator->trans('oro.pricing.productprice.import.strategy.tooltip'),       //configurar translate
            ImportExportConfiguration::FIELD_IMPORT_PROCESSORS_TO_CONFIRMATION_MESSAGE => [         //configurar translate
                'oro_pricing_product_price.reset' => $this->translator
                    ->trans('oro.pricing.productprice.import.strategy.reset_and_add_confirmation')
            ],
            ImportExportConfiguration::FIELD_IMPORT_ADDITIONAL_NOTICES => [
                $this->translator->trans('summa.productbadge.import.notice')                        //configurar translate
            ]
        ]);

//            ImportExportConfiguration::FIELD_ENTITY_CLASS => ProductPrice::class,
//            ImportExportConfiguration::FIELD_EXPORT_JOB_NAME => 'price_list_product_prices_export_to_csv',
//            ImportExportConfiguration::FIELD_EXPORT_PROCESSOR_ALIAS => 'oro_pricing_product_price',
//            ImportExportConfiguration::FIELD_EXPORT_TEMPLATE_PROCESSOR_ALIAS => 'oro_pricing_product_price',
//            ImportExportConfiguration::FIELD_IMPORT_PROCESSOR_ALIAS => 'oro_pricing_product_price.add_or_replace',
//            ImportExportConfiguration::FIELD_IMPORT_JOB_NAME => 'price_list_product_prices_entity_import_from_csv',
//            ImportExportConfiguration::FIELD_IMPORT_STRATEGY_TOOLTIP =>
//                $this->translator->trans('oro.pricing.productprice.import.strategy.tooltip'),
//            ImportExportConfiguration::FIELD_IMPORT_PROCESSORS_TO_CONFIRMATION_MESSAGE => [
//                'oro_pricing_product_price.reset' => $this->translator
//                     ->trans('oro.pricing.productprice.import.strategy.reset_and_add_confirmation')
//            ]
    }
}

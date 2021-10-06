<?php

namespace Summa\Bundle\BadgeBundle\Form\Type;

use Oro\Bundle\FrontendBundle\Form\Type\RuleEditorTextareaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Summa\Bundle\BadgeBundle\Form\OptionsConfigurator\BadgeRuleEditorOptionsConfigurator;

class BadgeRuleEditorType extends AbstractType
{
    const NAME = 'summa_badge_product_assignment_rule_editor';

    /**
     * @var BadgeRuleEditorOptionsConfigurator
     */
    private $optionsConfigurator;

    /**
     * @param BadgeRuleEditorOptionsConfigurator $optionsConfigurator
     */
    public function __construct(BadgeRuleEditorOptionsConfigurator $optionsConfigurator)
    {
        $this->optionsConfigurator = $optionsConfigurator;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $this->optionsConfigurator->configureOptions($resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $this->optionsConfigurator->limitNumericOnlyRules($view, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return RuleEditorTextareaType::class;
    }
}

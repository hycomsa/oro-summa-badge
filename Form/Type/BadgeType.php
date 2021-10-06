<?php

namespace Summa\Bundle\BadgeBundle\Form\Type;

use Oro\Bundle\CronBundle\Form\Type\ScheduleIntervalsCollectionType;
use Oro\Bundle\EntityExtendBundle\Form\Type\EnumSelectType;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Summa\Bundle\BadgeBundle\Entity\BadgeSchedule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Oro\Bundle\AttachmentBundle\Form\Type\ImageType;
use Summa\Bundle\BadgeBundle\Entity\Badge;

class BadgeType extends AbstractType
{
    const NAME = 'summa_badge_type';
    const SCHEDULES_FIELD = 'schedules';

    /** @var ManagerRegistry */
    private $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(
        ManagerRegistry $managerRegistry
    )
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('name',TextType::class,
                [
                    'required'      => true,
                    'label'         => 'summa.badge.name.label'
                ]
            )
            ->add(
                'position',
                EnumSelectType::class,
                [
                    'label'     => 'summa.badge.position.label',
                    'enum_code' => 'summa_badge_position',
                    'required'  => true,
                    'configs'   => ['allowClear' => false]
                ]
            )
            ->add(
                'active',
                CheckboxType::class,
                [
                    'label' => 'summa.badge.active.label'
                ]
            )
            ->add(
                'style',
                TextareaType::class,
                [
                    'label' => 'summa.badge.style.label',
                    'required'  => false
                ]
            )
            ->add('image',ImageType::class,
                array(
                    'required'  => false,
                    'mapped'    => false,
                    'label'         => 'summa.badge.image.label'
                )
            )
            ->add(
                'productAssignmentRule',
                BadgeRuleEditorType::class,
                [
                    'label' => 'summa.badge.product_assignment_rule.label',
                    'required' => false
                ]
            )
            ->add(
                'applyForNDays',
                TextType::class,
                [
                    'label' => 'summa.badge.apply_for_n_days.label',
                    'required' => false
                ]
            )
            ->add(
                self::SCHEDULES_FIELD,
                ScheduleIntervalsCollectionType::class,
                [
                    'label' => 'summa.badge.schedules.label',
                    'entry_options' => [
                        'data_class' => BadgeSchedule::class
                    ]
                ]
            )
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                /** @var Badge $badge */
                $badge = $event->getData();

                /** @var ImageType $fileUploaded */
                $fileUploaded = $event->getForm()->get('image')->getData();

                if ($fileUploaded){
                    $file = new File();
                    $file->setParentEntityClass(Badge::class);
                    $file->setParentEntityId($badge->getId());
                    $file->setFile($fileUploaded->getFile() );
                    $file->setOriginalFilename($fileUploaded->getOriginalFilename());

                    $this->managerRegistry->getManager()->persist($file);
                    $this->managerRegistry->getManager()->flush();
                    $badge->setImage($file);
                }
            },
            10
        );

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Badge::class,
        ]);
    }

    /**
     * @return string
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
     * @param $dataClass
     * @return $this
     */
    public function setDataClass($dataClass)
    {
        $this->dataClass = $dataClass;

        return $this;
    }

}

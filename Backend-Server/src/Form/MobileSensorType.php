<?php

namespace App\Form;

use App\Entity\Center;
use App\Entity\MobileSensor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MobileSensorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'documentation' => [
                    'type' => "string",
                    'example' => "center name"
                ]
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'DRONE' => MobileSensor::TYPE_DRONE,
                    'CAR_ROBOT' => MobileSensor::TYPE_CAR_ROBOT,
                ],
            ])
            ->add('status')
            ->add('longitude')
            ->add('latitude')
            ->add('center', EntityType::class, [
                'class' => Center::class,
                'choice_label' => 'id',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MobileSensor::class,
            'csrf_protection' => false,
        ]);
    }
}
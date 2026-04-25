<?php

namespace App\Form;

use App\Entity\Fire;
use App\Constant\FireStatusType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('forest', null, [
                'documentation' => [
                    'example' => 1
                ]
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'OnFire' => FireStatusType::OnFire->value,
                    'Completed' => FireStatusType::Completed->value,
                    'InProgress' => FireStatusType::InProgress->value,
                    'Canceled'=> FireStatusType::Canceled->value,
                ],
                'documentation' => [
                    'type' => 'string',
                    'enum' => [
                        FireStatusType::Completed->value,
                        FireStatusType::OnFire->value,
                        FireStatusType::InProgress->value,
                        FireStatusType::Canceled->value,
                    ],
                    'example' => FireStatusType::InProgress->value,
                ]
            ])
            ->add('device', null, [
                'documentation' => [
                    'example' => 1
                ]
            ]);
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Fire::class,
            'csrf_protection' => false,
        ]);
    }
}
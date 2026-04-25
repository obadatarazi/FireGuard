<?php

namespace App\Form;

use App\Entity\EmergencyRequest;
use App\Constant\EmergencyRequestStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmergencyRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('center', null, [
                'documentation' => [
                    'example' => 1
                ]
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'DANGEROUS' => EmergencyRequestStatus::Dangerous->value,
                    'DONE' => EmergencyRequestStatus::Done->value,
                ],
                'documentation' => [
                    'type' => 'string',
                    'enum' => [
                        EmergencyRequestStatus::Dangerous->value,
                        EmergencyRequestStatus::Done->value,
                    ],
                    'example' => EmergencyRequestStatus::Dangerous->value,
                ]
            ])
            ->add('fire', null, [
                'documentation' => [
                    'example' => 1
                ]
            ])->add('fireBrigade', null, [
                'documentation' => [
                    'example' => 1
                ]
            ]);
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EmergencyRequest::class,
            'csrf_protection' => false,
        ]);
    }
}
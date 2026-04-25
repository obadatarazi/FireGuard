<?php

namespace App\Form;

use App\Entity\Center;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CenterType extends AbstractType
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
            ->add('description', null, [
                'documentation' => [
                    'type' => "string",
                    'example' => "center description"
                ]
            ])
            ->add('phoneNumber', null, [
                'documentation' => [
                    'type' => "string",
                    'example' => "0933514147"
                ]
            ])
            ->add('status', null, [
                'documentation' => [
                    'type' => "string",
                    'example' => "status"
                ]
            ])->add('longitude', null, [
                'documentation' => [
                    'example' => '33.26225'
                ]
            ])
            ->add('latitude', null, [
                'documentation' => [
                    'example' => '32.62545'
                ]
            ])
            ->add('nameAddress', null, [
                'documentation' => [
                    'example' => 'damas'
                ]
            ]);
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Center::class,
            'csrf_protection' => false,
        ]);
    }
}
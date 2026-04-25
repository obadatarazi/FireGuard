<?php

namespace App\Form;

use App\Entity\Device;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeviceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'documentation' => [
                    'example' => "device name"
                ]
            ])
            ->add('description', null, [
                'documentation' => [
                    'example' => "device description"
                ]
            ])
            ->add('forest', null, [
                'documentation' => [
                    'example' => 1
                ]
            ])            ->add('longitude', null, [
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
            'data_class' => Device::class,
            'csrf_protection' => false,
        ]);
    }
}
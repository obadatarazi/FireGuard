<?php

namespace App\Form;

use App\Entity\DeviceValue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class DeviceValueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', null, [
                'documentation' => [
                    'example' => "Dangerous",
                ]
            ])
            ->add('valueHeat', null, [
                'documentation' => [
                    'type' => "float",
                    'example' => 10.11
                ]
            ])
            ->add('valueMoisture', null, [
                'documentation' => [
                    'type' => "float",
                    'example' => 12.11
                ]
            ])
            ->add('valueGas', null, [
                'documentation' => [
                    'type' => "float",
                    'example' => 20.11
                ]
            ])
            ->add('date', null, [
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy hh:mm:ss',
                'constraints' => [
                    new Assert\NotBlank()
                ],
                'documentation' => [
                    'example' => "1/01/2022 03:50:10"
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
            'data_class' => DeviceValue::class,
            'csrf_protection' => false,
        ]);
    }
}
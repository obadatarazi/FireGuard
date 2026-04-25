<?php

namespace App\Form;

use App\Entity\Car;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CarType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'documentation' => [
                    'type' => "string",
                    'example' => "KIA"
                ]
            ])
            ->add('numberPlate', null, [
                'documentation' => [
                    'type' => "string",
                    'example' => "645645"
                ]
            ])
            ->add('model', null, [
                'documentation' => [
                    'type' => "string",
                    'example' => "RIO"
                ]
            ])
            ->add('center', null, [
                'documentation' => [
                    'example' => 1,
                ]
            ]);
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Car::class,
            'csrf_protection' => false,
        ]);
    }
}
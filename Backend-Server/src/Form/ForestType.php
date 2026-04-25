<?php

namespace App\Form;

use App\Entity\Forest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ForestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'constraints' => [
                    new Assert\NotBlank()
                ],
                'documentation' => [
                    'example' => "forest name"
                ]
            ])
            ->add('description', null, [
                'documentation' => [
                    'example' => "forest description"
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
            'data_class' => Forest::class,
            'csrf_protection' => false,
        ]);
    }
}
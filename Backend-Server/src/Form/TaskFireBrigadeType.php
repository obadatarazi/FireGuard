<?php

namespace App\Form;

use App\Entity\TaskFireBrigade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskFireBrigadeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fire', null, [
                'documentation' => [
                    'example' => 1
                ]
            ])
            ->add('fireBrigade', null, [
                'documentation' => [
                    'example' => 1
                ]
            ])
            ->add('note', null, [
                'documentation' => [
                    'example' => "task fire brigade note"
                ]
            ])->add('status', null, [
                'documentation' => [
                    'example' => 'test'
                ]
            ]);
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TaskFireBrigade::class,
            'csrf_protection' => false,
        ]);
    }
}
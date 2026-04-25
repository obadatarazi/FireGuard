<?php

namespace App\Form;

use App\Entity\FireBrigade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class FireBrigadeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'documentation' => [
                    'type' => "string",
                    'example' => "fire brigade name"
                ]
            ])
            ->add('numberOfTeam', null, [
                'documentation' => [
                    'type' => "integer",
                    'example' => 10
                ]
            ])
            ->add('center', null, [
                'documentation' => [
                    'type' => "integer",
                    'example' => 1
                ]
            ])
            ->add('email', null, [
                'documentation' => [
                    'example' => 'test@test.com'
                ]
            ])
            ->add('password', null, [
                'mapped' => false,
                'documentation' => [
                    'example' => "test@123"
                ],
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/(?=.*[0-9])+(?=.*[a-z])/',
                        'message' => 'invalid_password',
                    ]),
                    new Assert\Length([
                        'min' => 8
                    ]),
                ]]
            );
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FireBrigade::class,
            'csrf_protection' => false,
        ]);
    }
}
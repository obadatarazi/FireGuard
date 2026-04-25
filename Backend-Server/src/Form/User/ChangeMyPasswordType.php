<?php

namespace App\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ChangeMyPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('currentPassword', null, [
                'mapped' => false,
                'documentation' => [
                    'example' => "svc123456"
                ]
            ])
            ->add('newPassword', null, [
                'mapped' => false,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Regex([
                        'pattern' => '/(?=.*[0-9])+(?=.*[a-z])/',
                        'message' => 'invalid_password',
                    ]),
                    new Assert\Length([
                        'min' => 8
                    ]),
                ], 'documentation' => [
                    'example' => "svc123456"
                ]
            ])->add('confirmPassword', null, [
                'mapped' => false,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => 8
                    ])
                ], 'documentation' => [
                    'example' => "svc123456"
                ]
            ]);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false
        ]);
    }
}

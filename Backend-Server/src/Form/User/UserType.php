<?php

namespace App\Form\User;

use App\Constant\UserGender;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $edit = $options['edit'];

        $builder
            ->add('email', null, [
                'documentation' => [
                    'example' => "email@email.com"
                ]
            ])
            ->add('fullName', null, [
                'documentation' => [
                    'example' => "superman"
                ]
            ])
            ->add('phoneNumber', null, [
                'documentation' => [
                    'example' => "9442397123"
                ]
            ])
            ->add('gender', null, [
                'constraints' => [
                    new Assert\NotBlank()
                ],
                'documentation' => [
                    'type' => 'string',
                    'enum' => [UserGender::Male->value, UserGender::Female->value],
                    'example' => UserGender::Male->value,
                ]
            ])

            ->add('dateOfBirth', null, [
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'constraints' => [
                    new Assert\NotBlank()
                ],
                'documentation' => [
                    'example' => "1/01/2022"
                ]
            ])
            ->add('avatar', FileType::class, [
                'mapped' => false,
                'constraints' => [
                    new Assert\File([
                        "maxSize" => "10M",
                        "maxSizeMessage" => "Max file size is 10MB",
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'The image must be jpg|png|gif',
                    ]),
                ]
            ])
            ->add('rolesGroups', null, [
                'documentation' => [
                    'example' => 1
                ]
            ]);

            if (!$edit) {
                $builder->add('password', null, [
                'mapped' => false,
                'documentation' => [
                    'example' => "svc123456"
                ],
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/(?=.*[0-9])+(?=.*[a-z])/',
                        'message' => 'invalid_password',
                    ]),
                    new Assert\Length([
                        'min' => 8
                    ]),
                ]
            ]);
        }
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => false,
            'edit' => false,
        ]);
    }
}
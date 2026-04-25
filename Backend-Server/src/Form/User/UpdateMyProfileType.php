<?php

namespace App\Form\User;

use App\Constant\UserGender;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateMyProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('avatar', FileType::class, [
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
            ]])

            ->add('phoneNumber', null, [
                'documentation' => [
                    'example' => "9442397123",
                ]])

            ->add('email', null, [
                'documentation' => [
                    'example' => "email@email.com",
                ],
            ])
            ->add('gender', null, [
                'documentation' => [
                    'type' => 'string',
                    'enum' => [UserGender::Male->value, UserGender::Female->value],
                    'example' => UserGender::Male->value,
                ],
            ])
            ->add('dateOfBirth', null, [
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'documentation' => [
                    'example' => "1/01/2022",
                ],
            ])
            ->add('fullName', null, [
                'documentation' => [
                    'example' => "superman",
                ],
            ])
            ->add('area', null, [
                'constraints' => [
                    new Assert\NotBlank()
                ],
                'documentation' => [
                    'example' => 1
                ]
            ])
            ->add('governorate', null, [
                'constraints' => [
                    new Assert\NotBlank()
                ],
                'documentation' => [
                    'example' => 1
                ]
            ])
            ->add('address', null, [
                'documentation' => [
                    'example' => 'white bridge'
                ]
            ])
            ->add('nationalNumber', null, [
                'documentation' => [
                    'example' => '0101-8433-2397'
                ]
            ])
            ->add('identityRestriction', null, [
                'documentation' => [
                    'example' => '2345-2934-2934'
                ]
            ])
            ->add('bankName', null, [
                'documentation' => [
                    'example' => 'Syria International Islamic Bank'
                ]
            ])
            ->add('bankAccountNumber', null, [
                'documentation' => [
                    'example' => '2394-3454-3454'
                ]
            ])
            ->add('contractStartDate',null, [
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
            ->add('contractEndDate',null, [
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
            ->add('copyConcludedContract', FileType::class, [
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
            ->add('identityFrontFace', FileType::class, [
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
            ->add('identityBackFace', FileType::class, [
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => false,
        ]);
    }
}

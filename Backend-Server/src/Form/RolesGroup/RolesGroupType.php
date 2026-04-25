<?php

namespace App\Form\RolesGroup;

use App\Entity\RolesGroup;
use App\Service\RolesService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RolesGroupType extends AbstractType
{
    private RolesService $rolesService;

    public function __construct(RolesService $rolesService)
    {
        $this->rolesService = $rolesService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("name", null, [
                'documentation' => [
                    'example' => 'Super Admin',
                    'type' => 'string'
                ]
            ])
            ->add("roles", CollectionType::class,
                [
                    'allow_add' => true,
                    'entry_type' => ChoiceType::class,
                    'entry_options' => [
                        'choices' => $this->rolesService->getAll()
                    ],
                    'constraints' => [new Assert\NotBlank()],
                    'mapped' => false,
                ]);

    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RolesGroup::class,
            'csrf_protection' => false,
        ]);
    }
}
<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                "label" => "Nom d'utilisateur",
                'label_attr' => ['class' => 'label'],
                'attr' => ['class' => 'input input-bordered', 'placeholder' => 'jdoe'],
                "mapped" => true,
                "required" => true,
                "trim" => true,
                'constraints' => [
                    new NotBlank(['message' => "Le nom d'utilisateur est obligatoire."]),
                    new Length([
                        'min' => 4,
                        'max' => 25,
                        'minMessage' => "Le nom d'utilisateur doit contenir au moins {{ limit }} caractères.",
                        'maxMessage' => "Le nom d'utilisateur ne peut pas dépasser {{ limit }} caractères."
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-Z0-9_]+$/',
                        'message' => "Le nom d'utilisateur ne peut contenir que des lettres, des chiffres et des underscores."
                    ]),
                ]
            ])
            ->add('email', EmailType::class, [
                "label" => "E-Mail",
                'label_attr' => ['class' => 'label'],
                'attr' => ['class' => 'input input-bordered', 'placeholder' => 'jdoe@gmail.com'],
                "mapped" => true,
                "required" => true,
                "trim" => true,
                'constraints' => [
                    new NotBlank(['message' => "L'adresse e-mail est obligatoire."]),
                    new Email(['message' => "Veuillez saisir une adresse e-mail valide."]),
                ]
            ])
            ->add('name', TextType::class, [
                "label" => "Nom",
                'label_attr' => ['class' => 'label'],
                'attr' => ['class' => 'input input-bordered', 'placeholder' => 'Doe'],
                "mapped" => true,
                "required" => true,
                "trim" => true,
                'constraints' => [
                    new NotBlank(['message' => "Le nom est obligatoire."]),
                    new Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => "Le nom doit contenir au moins {{ limit }} caractères.",
                        'maxMessage' => "Le nom ne peut pas dépasser {{ limit }} caractères."
                    ]),
                ]
            ])
            ->add('firstname', TextType::class, [
                "label" => "Prénom",
                'label_attr' => ['class' => 'label'],
                'attr' => ['class' => 'input input-bordered', 'placeholder' => 'John'],
                "mapped" => true,
                "required" => true,
                "trim" => true,
                'constraints' => [
                    new NotBlank(['message' => "Le prénom est obligatoire."]),
                    new Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => "Le prénom doit contenir au moins {{ limit }} caractères.",
                        'maxMessage' => "Le prénom ne peut pas dépasser {{ limit }} caractères."
                    ]),
                ]
            ])
            ->add('service', TextType::class, [
                "label" => "Service",
                'label_attr' => ['class' => 'label'],
                'attr' => ['class' => 'input input-bordered', 'placeholder' => 'Rechercher...', 'list' => 'serviceLst'],
                "mapped" => false,
                "required" => true,
                "trim" => true,
                'constraints' => [
                    new NotBlank(['message' => "Le service est obligatoire."]),
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

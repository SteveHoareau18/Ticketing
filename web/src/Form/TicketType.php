<?php

namespace App\Form;

use App\Entity\Ticket;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

use Symfony\Component\Validator\Constraints as Assert;

class TicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('problem', TextareaType::class, [
                "label" => "Raison de l'ouverture du ticket",
                'label_attr' => ['class' => 'label'],
                'attr' => [
                    'class' => 'input input-bordered',
                    'placeholder' => 'Le client CL001 a besoin de...'
                ],
                "mapped" => true,
                "required" => true,
                "trim" => true,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Ce champ ne peut pas être vide.',
                    ]),
                    new Assert\Length([
                        'min' => 10,
                        'minMessage' => 'La raison doit contenir au moins {{ limit }} caractères.',
                        'max' => 1000,
                        'maxMessage' => 'La raison ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('service', TextType::class, [
                "label" => "Service",
                'label_attr' => ['class' => 'label'],
                'attr' => ['class' => 'input input-bordered', 'placeholder' => 'Rechercher...', 'list' => 'serviceLst'],
                "mapped" => false,
                "required" => true,
                "trim" => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
        ]);
    }
}

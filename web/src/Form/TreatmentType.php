<?php

namespace App\Form;

use App\Entity\Treatment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class TreatmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('observations', TextareaType::class, [
                "label" => "Qu'avez-vous constaté ?",
                'label_attr' => ['class' => 'label'],
                'attr' => ['class' => 'textarea input-bordered', 'placeholder' => "C'est fait/Il manque encore..."],
                "mapped" => true,
                "required" => true,
                "trim" => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Les observations sont obligatoires.',
                    ]),
                    new Length([
                        'min' => 10,
                        'max' => 500,
                        'minMessage' => 'Les observations doivent contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Les observations ne peuvent pas dépasser {{ limit }} caractères.'
                    ]),
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Treatment::class,
        ]);
    }
}

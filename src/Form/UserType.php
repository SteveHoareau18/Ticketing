<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                "label"=>"Identifiant",
                'label_attr'=>['class'=>'label'],
                'attr'=>['class'=>'input input-bordered','placeholder'=>'jdoe'],
                "mapped"=>true,
                "required"=>true,
                "trim"=>true
            ])
            ->add('name', TextType::class, [
                "label"=>"Nom",
                'label_attr'=>['class'=>'label'],
                'attr'=>['class'=>'input input-bordered','placeholder'=>'Doe'],
                "mapped"=>true,
                "required"=>true,
                "trim"=>true
            ])
            ->add('firstname', TextType::class, [
                "label"=>"Prénom",
                'label_attr'=>['class'=>'label'],
                'attr'=>['class'=>'input input-bordered','placeholder'=>'John'],
                "mapped"=>true,
                "required"=>true,
                "trim"=>true
            ])
            ->add('service', TextType::class, [
                "label"=>"Service",
                'label_attr'=>['class'=>'label'],
                'attr'=>['class'=>'input input-bordered','placeholder'=>'Rechercher...','list'=>'serviceLst'],
                "mapped"=>false,
                "required"=>true,
                "trim"=>true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

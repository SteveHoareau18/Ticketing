<?php

namespace App\Form;

use App\Entity\Ticket;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('problem', TextareaType::class,[
                "label"=>"Raison de l'ouverture du ticket",
                'label_attr'=>['class'=>'label'],
                'attr'=>['class'=>'input input-bordered','placeholder'=>'Le client CL001 a besoin de...'],
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
            'data_class' => Ticket::class,
        ]);
    }
}

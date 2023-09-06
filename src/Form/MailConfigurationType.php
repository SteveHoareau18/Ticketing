<?php

namespace App\Form;

use App\Entity\MailConfiguration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class MailConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('subject', TextType::class, [
                'label' => 'Nom de l\'expéditeur (sujet de chaque mail)',
                'constraints' => [
                    new NotBlank()
                ],
                'attr' => ['class' => 'input input-bordered w-full max-w-xs', 'placeholder' => 'Nom de l\'expéditeur (sujet de chaque mail)'],
                'trim' => true,
                'required' => false
            ])
            ->add('login', EmailType::class, [
                'label' => 'Adresse e-mail',
                'constraints' => [
                    new NotBlank()
                ],
                'attr' => ['class' => 'input input-bordered w-full max-w-xs', 'placeholder' => 'Adresse e-mail'],
                'trim' => true,
                'required' => false
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'constraints' => [
                    new NotBlank()
                ],
                'attr' => ['class' => 'input input-bordered w-full max-w-xs', 'placeholder' => 'Mot de passe'],
                'trim' => true,
                'required' => false
            ])
            ->add('smtpAddress', TextType::class, [
                'label' => 'Adresse du serveur SMTP',
                'attr' => ['class' => 'input input-bordered w-full max-w-xs', 'placeholder' => 'Adresse du serveur SMTP'],
                'trim' => true,
                'required' => false
            ])
            ->add('smtpPort', IntegerType::class, [
                'label' => 'Port SMTP',
                'attr' => ['class' => 'input input-bordered w-full max-w-xs', 'placeholder' => 'Port SMTP'],
            ])
            ->add('smtpTls', CheckboxType::class, [
                'label' => 'SMTP TLS actif',
                'row_attr' => [
                    'class' => 'text-center p-5'
                ],
                'attr' => ['class' => 'checkbox checkbox-xs'],
                'required' => false
            ])
            ->add('ccAddress', TextType::class, [
                'label' => 'Adresse(s) de réception CarbonCopy, (séparées par "," si multiples)',
                'constraints' => [
                    new NotBlank()
                ],
                'attr' => ['class' => 'input input-bordered w-full max-w-xs', 'placeholder' => 'Adresse(s) de réception, (séparées par "," si multiples)'],
                'trim' => true,
                'required' => false
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Confirmer',
                'attr' => ['class' => 'btn btn-primary']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MailConfiguration::class,
        ]);
    }
}
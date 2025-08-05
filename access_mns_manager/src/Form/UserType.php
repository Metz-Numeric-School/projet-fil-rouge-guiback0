<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Service;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class)
            ->add('password', PasswordType::class)
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('date_naissance', DateType::class, [
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('telephone', TelType::class, [
                'required' => false
            ])
            ->add('adresse', TextType::class, [
                'required' => false
            ])
            ->add('poste', TextType::class, [
                'required' => false
            ])
            ->add('horraire', TimeType::class, [
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('heure_debut', TimeType::class, [
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('jours_semaine_travaille', IntegerType::class, [
                'required' => false,
                'attr' => [
                    'min' => 1,
                    'max' => 7
                ]
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Manager' => 'ROLE_MANAGER',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ]);

        // Add secondary services selection only in organisation context
        if ($options['organisation_context'] && !empty($options['available_services'])) {
            $builder->add('secondary_services', EntityType::class, [
                'class' => Service::class,
                'choices' => $options['available_services'],
                'choice_label' => 'nomService',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'mapped' => false,
            ]);
        }

        // Auto-set date_inscription if not in organisation context
        if (!$options['organisation_context']) {
            $builder->add('date_inscription', DateType::class, [
                'widget' => 'single_text',
                'data' => new \DateTime(),
                'required' => false
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'organisation_context' => false,
            'available_services' => [],
        ]);
    }
}

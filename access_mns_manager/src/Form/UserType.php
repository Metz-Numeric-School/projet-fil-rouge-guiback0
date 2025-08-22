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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'help' => 'Utilisée pour la connexion et les notifications'
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'help' => $options['is_edit'] ? 'Laissez vide pour conserver le mot de passe actuel' : 'Minimum 6 caractères',
                'required' => !$options['is_edit'],
                'mapped' => false,
                'attr' => [
                    'autocomplete' => 'new-password'
                ]
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom de famille'
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom'
            ])
            ->add('date_naissance', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Date de naissance',
                'help' => 'Optionnel - Format: JJ/MM/AAAA'
            ])
            ->add('telephone', TelType::class, [
                'required' => false,
                'label' => 'Numéro de téléphone',
                'help' => 'Format français recommandé: 01 23 45 67 89'
            ])
            ->add('poste', TextType::class, [
                'required' => false,
                'label' => 'Fonction / Poste',
                'help' => 'Titre du poste occupé dans l\'organisation'
            ])
            ->add('horraire', TimeType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Durée journalière de travail',
                'help' => 'Nombre d\'heures travaillées par jour'
            ])
            ->add('heure_debut', TimeType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Heure de début de journée',
                'help' => 'Heure habituelle d\'arrivée'
            ])
            ->add('jours_semaine_travaille', IntegerType::class, [
                'required' => false,
                'label' => 'Jours travaillés par semaine',
                'help' => 'Nombre de jours (1 à 7)',
                'attr' => [
                    'min' => 1,
                    'max' => 7,
                    'placeholder' => '5'
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
                'label' => 'Rôles et permissions',
                'help' => 'Sélectionnez un ou plusieurs rôles selon les responsabilités'
            ])
            ->add('compte_actif', CheckboxType::class, [
                'required' => false,
                'label' => 'Compte utilisateur actif',
                'help' => 'Décochez pour désactiver le compte (conservation 5 ans - conformité RGPD)'
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

        // Add GDPR compliance fields for admins only
        if ($options['show_admin_fields']) {
            $builder
                ->add('date_derniere_connexion', DateTimeType::class, [
                    'widget' => 'single_text',
                    'required' => false,
                    'disabled' => true,
                    'label' => 'Dernière connexion'
                ])
                ->add('date_suppression_prevue', DateType::class, [
                    'widget' => 'single_text',
                    'required' => false,
                    'label' => 'Date de suppression prévue',
                    'help' => 'Date automatique de suppression après désactivation (5 ans)'
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'organisation_context' => false,
            'available_services' => [],
            'show_admin_fields' => false,
            'is_edit' => false,
        ]);
    }
}

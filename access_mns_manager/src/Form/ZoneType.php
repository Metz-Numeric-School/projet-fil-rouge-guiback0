<?php

namespace App\Form;

use App\Entity\Zone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ZoneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_zone', null, [
                'label' => 'Nom de la zone',
                'attr' => [
                    'placeholder' => 'Ex: Zone Serveurs, Zone Réunion, Zone Archives...'
                ]
            ])
            ->add('description', null, [
                'label' => 'Description',
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Décrivez l\'utilisation de cette zone, les équipements présents, les restrictions d\'accès...'
                ]
            ])
            ->add('capacite', null, [
                'label' => 'Capacité maximale',
                'help' => 'Nombre maximum de personnes autorisées simultanément',
                'attr' => [
                    'placeholder' => 'Ex: 50'
                ]
            ]);

        // Note: Organisation relationship is managed through ServiceZone, not directly on Zone
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Zone::class,
            'from_service' => false,
        ]);
    }
}

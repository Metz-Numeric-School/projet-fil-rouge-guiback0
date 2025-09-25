<?php

namespace App\Form;

use App\Entity\Badge;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BadgeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numero_badge', null, [
                'label' => 'Numéro de badge',
                'attr' => [
                    'placeholder' => 'Ex: 12345'
                ]
            ])
            ->add('type_badge', null, [
                'label' => 'Type de badge',
                'attr' => [
                    'placeholder' => 'Ex: RFID, NFC, MIFARE...'
                ]
            ])
            ->add('date_creation', null, [
                'widget' => 'single_text',
                'label' => 'Date de création',
                'data' => new \DateTime()
            ])
            ->add('date_expiration', null, [
                'widget' => 'single_text',
                'label' => 'Date d\'expiration',
                'required' => false,
                'help' => 'Laissez vide pour un badge sans expiration'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Badge::class,
        ]);
    }
}

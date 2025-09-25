<?php

namespace App\Form;

use App\Entity\Badgeuse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BadgeuseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reference', null, [
                'label' => 'Référence de la badgeuse',
                'help' => 'Identifiant unique (lettres majuscules, chiffres, tirets et underscores uniquement)',
                'attr' => [
                    'placeholder' => 'Ex: BADGE_001, READER-A1...'
                ]
            ])
            ->add('date_installation', null, [
                'widget' => 'single_text',
                'label' => 'Date d\'installation',
                'data' => new \DateTime()
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Badgeuse::class,
        ]);
    }
}

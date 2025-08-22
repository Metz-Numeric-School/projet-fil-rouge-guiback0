<?php

namespace App\Form;

use App\Entity\Acces;
use App\Entity\Badgeuse;
use App\Entity\Zone;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_acces')
            ->add('date_installation', null, [
                'widget' => 'single_text'
            ])
            ->add('zone', EntityType::class, [
                'class' => Zone::class,
                'choice_label' => 'nom_zone',
            ])
            ->add('badgeuse', EntityType::class, [
                'class' => Badgeuse::class,
                'choice_label' => 'reference',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Acces::class,
        ]);
    }
}

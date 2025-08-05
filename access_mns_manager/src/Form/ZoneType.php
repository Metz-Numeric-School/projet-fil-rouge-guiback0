<?php

namespace App\Form;

use App\Entity\Organisation;
use App\Entity\Zone;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ZoneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_zone')
            ->add('description')
            ->add('capacite');

        // Only add organisation field if not creating from service context
        if (!$options['from_service']) {
            $builder->add('organisation', EntityType::class, [
                'class' => Organisation::class,
                'choice_label' => 'nomOrganisation',
                'placeholder' => 'Sélectionner une organisation',
                'required' => true,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Zone::class,
            'from_service' => false,
        ]);
    }
}

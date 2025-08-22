<?php

namespace App\Form;

use App\Entity\Organisation;
use App\Entity\Service;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_service')
            ->add('niveau_service');
            
        // Only add organisation field if not creating from organisation context
        if (!$options['hide_organisation']) {
            $builder->add('organisation', EntityType::class, [
                'class' => Organisation::class,
                'choice_label' => 'nom_organisation',
                'disabled' => $options['read_only_organisation'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Service::class,
            'hide_organisation' => false,
            'read_only_organisation' => false,
        ]);
    }
}

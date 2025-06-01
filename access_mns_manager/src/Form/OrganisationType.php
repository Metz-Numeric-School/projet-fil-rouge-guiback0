<?php

namespace App\Form;

use App\Entity\Organisation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrganisationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_organisation')
            ->add('telephone')
            ->add('email')
            ->add('site_web')
            ->add('siret')
            ->add('ca')
            ->add('numero_rue')
            ->add('suffix_rue')
            ->add('nom_rue')
            ->add('code_postal')
            ->add('ville')
            ->add('pays')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Organisation::class,
        ]);
    }
}

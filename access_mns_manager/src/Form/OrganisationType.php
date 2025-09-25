<?php

namespace App\Form;

use App\Entity\Organisation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrganisationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_organisation', TextType::class, [
                'label' => 'Nom de l\'organisation',
                'attr' => [
                    'placeholder' => 'Nom de votre organisation'
                ]
            ])
            ->add('telephone', TextType::class, [
                'required' => false,
                'label' => 'Téléphone',
                'attr' => [
                    'placeholder' => '01 23 45 67 89'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'contact@organisation.com'
                ]
            ])
            ->add('site_web', UrlType::class, [
                'required' => false,
                'label' => 'Site web',
                'attr' => [
                    'placeholder' => 'https://www.organisation.com'
                ]
            ])
            ->add('siret', TextType::class, [
                'required' => false,
                'label' => 'SIRET',
                'attr' => [
                    'placeholder' => '12345678901234'
                ]
            ])
            ->add('ca', NumberType::class, [
                'required' => false,
                'label' => 'Chiffre d\'affaires (€)',
                'attr' => [
                    'placeholder' => '1000000'
                ]
            ])
            ->add('numero_rue', IntegerType::class, [
                'required' => false,
                'label' => 'N°',
                'attr' => [
                    'placeholder' => '123'
                ]
            ])
            ->add('suffix_rue', TextType::class, [
                'required' => false,
                'label' => 'Suffixe',
                'attr' => [
                    'placeholder' => 'bis'
                ]
            ])
            ->add('nom_rue', TextType::class, [
                'label' => 'Nom de la rue',
                'attr' => [
                    'placeholder' => 'Ex: rue de la République'
                ]
            ])
            ->add('code_postal', TextType::class, [
                'required' => false,
                'label' => 'Code postal',
                'attr' => [
                    'placeholder' => '75001'
                ]
            ])
            ->add('ville', TextType::class, [
                'required' => false,
                'label' => 'Ville',
                'attr' => [
                    'placeholder' => 'Paris'
                ]
            ])
            ->add('pays', TextType::class, [
                'required' => false,
                'label' => 'Pays',
                'attr' => [
                    'placeholder' => 'France'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Organisation::class,
        ]);
    }
}

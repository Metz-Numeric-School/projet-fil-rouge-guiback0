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
use Symfony\Component\Validator\Constraints as Assert;

class OrganisationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_organisation', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom de l\'organisation est obligatoire.']),
                    new Assert\Length(['max' => 255])
                ]
            ])
            ->add('telephone', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => 255])
                ]
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'email est obligatoire.']),
                    new Assert\Email(['message' => 'Veuillez saisir un email valide.']),
                    new Assert\Length(['max' => 255])
                ]
            ])
            ->add('site_web', UrlType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Url(['message' => 'Veuillez saisir une URL valide.']),
                    new Assert\Length(['max' => 255])
                ]
            ])
            ->add('siret', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => 14])
                ]
            ])
            ->add('ca', NumberType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\PositiveOrZero(['message' => 'Le chiffre d\'affaires doit être positif.'])
                ]
            ])
            ->add('numero_rue', IntegerType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Positive(['message' => 'Le numéro de rue doit être positif.'])
                ]
            ])
            ->add('suffix_rue', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => 10])
                ]
            ])
            ->add('nom_rue', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom de rue est obligatoire.']),
                    new Assert\Length(['max' => 255])
                ],
                'attr' => [
                    'placeholder' => 'Ex: rue de la République'
                ]
            ])
            ->add('code_postal', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => 255])
                ]
            ])
            ->add('ville', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => 255])
                ]
            ])
            ->add('pays', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => 255])
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

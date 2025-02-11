<?php

namespace App\Form;

use App\Entity\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('portrait', FileType::class, [
                'label' => 'Portrait',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'accept' => 'image/*',
                ],
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'required' => false,
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'required' => false,
            ])
            ->add('birthName', TextType::class, [
                'label' => 'Nom de naissance',
                'required' => false,
            ])
            ->add('otherNames', TextType::class, [
                'label' => 'Autres noms',
                'required' => false,
            ])
            ->add('bio', TextareaType::class, [
                'required' => false,
            ])
            ->add('birth', DateType::class, [
                'label' => 'Date de naissance',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('birthPlace', TextType::class, [
                'label' => 'Lieu de naissance',
                'required' => false,
            ])
            ->add('birthDayUnsure', CheckboxType::class, [
                'required' => false,
                'label' => 'Jour incertain',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch'
                ],
                'row_attr' => [
                    'class' => 'd-inline'
                ],
            ])
            ->add('birthMonthUnsure', CheckboxType::class, [
                'required' => false,
                'label' => 'Mois incertain',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch'
                ],
                'row_attr' => [
                    'class' => 'd-inline'
                ],
            ])
            ->add('birthYearUnsure', CheckboxType::class, [
                'required' => false,
                'label' => 'Année incertaine',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch'
                ],
                'row_attr' => [
                    'class' => 'd-inline'
                ],
            ])
            ->add('death', DateType::class, [
                'label' => 'Date de décès',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('deathPlace', TextType::class, [
                'label' => 'Lieu de décès',
                'required' => false,
            ])
            ->add('deathDayUnsure', CheckboxType::class, [
                'required' => false,
                'label' => 'Jour incertain',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch'
                ],
                'row_attr' => [
                    'class' => 'd-inline'
                ],
            ])
            ->add('deathMonthUnsure', CheckboxType::class, [
                'required' => false,
                'label' => 'Mois incertain',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch'
                ],
                'row_attr' => [
                    'class' => 'd-inline'
                ],
            ])
            ->add('deathYearUnsure', CheckboxType::class, [
                'required' => false,
                'label' => 'Année incertaine',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch'
                ],
                'row_attr' => [
                    'class' => 'd-inline'
                ],
            ])
            ->add('dead', CheckboxType::class, [
                'required' => false,
                'label' => 'Décédé',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch'
                ],
                'row_attr' => [
                    'class' => 'd-inline'
                ],
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'Genre',
                'choices' => [
                    'Homme' => Person::MALE,
                    'Femme' => Person::FEMALE,
                    'Autre' => Person::OTHER,
                ],
                'expanded' => true,
                'label_attr' => [
                    'class' => 'radio-inline'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
        ]);
    }
}

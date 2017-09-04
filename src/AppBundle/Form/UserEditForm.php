<?php

namespace AppBundle\Form;

use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserEditForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', null, [
                'disabled' => true,
                'attr' => [
                    'class' => 'js-email'
                ]
            ])
            ->add('name', null, [
                'attr' => [
                    'class' => 'js-name'
                ]
            ])
            ->add('country', ChoiceType::class, [
                'choices' => [
                    'bg.CountryName' => 'bg',
                    'ro.CountryName' => 'ro'
                ],
                'attr' => [
                    'class' => 'js-countryCode'
                ]
            ])
            ->add('postCode', null, [
                'attr' => [
                    'class' => 'js-postCode'
                ]
            ])
            ->add('province', null, [
                'attr' => [
                    'class' => 'js-province'
                ]
            ])
            ->add('city', null, [
                'attr' => [
                    'class' => 'js-city'
                ]
            ])
            ->add('address')
            ->add(
                $builder->create('location', FormType::class, ['compound' => true])
                    ->add('latitude', TextType::class)
                    ->add('longitude', TextType::class)
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'form',
            'validation_groups' => ['Default']
        ]);
    }

}

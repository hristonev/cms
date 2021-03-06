<?php

namespace AppBundle\Form;

use AppBundle\Entity\User;
use Captcha\Bundle\CaptchaBundle\Form\Type\CaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserRegistrationForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class)
            ->add('name')
            ->add('captchaCode', CaptchaType::class, array(
                'captchaConfig' => 'DefaultCaptcha'
            ));
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'form',
            'validation_groups' => ['Default', 'Registration']
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_user_registration_form';
    }
}

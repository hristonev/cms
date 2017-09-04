<?php

namespace AppBundle\Form;

use AppBundle\Entity\User;
use Captcha\Bundle\CaptchaBundle\Form\Type\CaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RestorePasswordForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', null)
            ->add('captchaCode', CaptchaType::class, array(
                'captchaConfig' => 'DefaultCaptcha'
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'form',
            'validation_groups' => ['Default', 'Restore']
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_restore_password_form';
    }
}

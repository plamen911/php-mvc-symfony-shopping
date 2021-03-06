<?php

namespace AppBundle\Form;

use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SignupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', TextType::class, ['label' => 'E-mail', 'required' => false, 'attr' => ['placeholder' => 'E-mail...']])
            ->add('firstName', TextType::class, ['label' => 'First Name', 'required' => false, 'attr' => ['placeholder' => 'First Name...']])
            ->add('lastName', TextType::class, ['label' => 'Last Name', 'required' => false, 'attr' => ['placeholder' => 'Last Name...']])
            ->add('phone', TextType::class, ['label' => 'Phone', 'required' => false, 'attr' => ['placeholder' => 'Phone...']])
            ->add('password', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'first_options'  => ['label' => 'Password', 'attr' => ['placeholder' => 'Password...']],
                    'second_options' => ['label' => 'Repeat Password', 'attr' => ['placeholder' => 'Repeat Password...']],
                ]
            )
            ->add('signup', SubmitType::class, ['label' => 'Create My Account', 'attr' => ['class' => 'button expand']]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_signup_type';
    }
}

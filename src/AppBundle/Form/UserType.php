<?php

namespace AppBundle\Form;

use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, ['label' => 'First Name', 'attr' => ['placeholder' => 'First Name']])
            ->add('lastName', TextType::class, ['label' => 'Last Name', 'attr' => ['placeholder' => 'Last Name']])
            ->add('email', EmailType::class, ['label' => 'E-mail', 'attr' => ['placeholder' => 'E-mail']])
            ->add('password', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'first_options'  => ['label' => 'Password', 'attr' => ['placeholder' => 'Password']],
                    'second_options' => ['label' => 'Repeat Password', 'attr' => ['placeholder' => 'Repeat Password']],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_user_type';
    }
}

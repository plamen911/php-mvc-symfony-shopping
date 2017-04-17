<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StoreOrderType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('billingFirstName')
            ->add('billingLastName')
            ->add('billingEmail')
            ->add('billingPhone')
            ->add('billingAddress')
            ->add('billingAddress2')
            ->add('billingCity')
            ->add('billingState')
            ->add('billingZip')
            ->add('shippingFirstName')
            ->add('shippingLastName')
            ->add('shippingEmail')
            ->add('shippingPhone')
            ->add('shippingAddress')
            ->add('shippingAddress2')
            ->add('shippingCity')
            ->add('shippingState')
            ->add('shippingZip')
            ->add('creditCardType')
            ->add('creditCardNumber')
            ->add('creditCardExpDate')
            ->add('creditCardYear')
            ->add('creditCardMonth')
            ->add('creditCardName');
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\StoreOrder'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_storeorder';
    }


}

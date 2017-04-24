<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StoreOrderType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $cards = [
            'AmEx' => 'amex',
            'Master Card' => 'mastercard',
            'Visa' => 'visa',
        ];

        $year = date('Y');
        $months = [];
        for ($mm = 1; $mm <= 12; $mm++) {
            $months[date('F', mktime(0, 0, 0, $mm, 1, $year))] = sprintf("%02d", $mm);
        }

        $years = [];
        for ($yy = $year; $yy <= $year + 10; $yy++) {
            $years[$yy] = $yy;
        }

        $builder
            ->add('billingFirstName')
            ->add('billingLastName')
            ->add('billingEmail')
            ->add('billingPhone')
            ->add('billingAddress')
            ->add('billingAddress2')
            ->add('billingCity')
            ->add('billingState')
            ->add('billingZip', IntegerType::class)
            ->add('shippingFirstName')
            ->add('shippingLastName')
            ->add('shippingEmail')
            ->add('shippingPhone')
            ->add('shippingAddress')
            ->add('shippingAddress2')
            ->add('shippingCity')
            ->add('shippingState')
            ->add('shippingZip', IntegerType::class, ['label' => 'Zip', 'attr' => ['placeholder' => 'Zip...']])
            ->add('creditCardType', ChoiceType::class, [
                    'label' => 'Credit Card Type',
                    'choices' => $cards,
                    'required' => false,
                    'placeholder' => '- select -',
                    'empty_data' => null
                ]
            )
            ->add('creditCardNumber', IntegerType::class, ['label' => 'Credit Card Number', 'attr' => ['placeholder' => 'Credit Card Number...']])
            ->add('creditCardMonth', ChoiceType::class, [
                    'choices' => $months,
                    'required' => false,
                    'placeholder' => 'MM',
                    'empty_data' => null
                ]
            )
            ->add('creditCardYear', ChoiceType::class, [
                    'choices' => $years,
                    'required' => false,
                    'placeholder' => 'YYYY',
                    'empty_data' => null
                ]
            )
            ->add('creditCardCode', IntegerType::class, ['label' => 'Security Code', 'attr' => ['placeholder' => 'Security Code...']])
            ->add('creditCardName', TextType::class, ['label' => 'Name on Card', 'attr' => ['placeholder' => 'Name on Card...']]);
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

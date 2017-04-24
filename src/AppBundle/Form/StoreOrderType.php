<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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

        $states = $options['states'];

        $builder
            ->add('billingFirstName',TextType::class, ['label' => 'First Name', 'attr' => ['placeholder' => 'First Name...']])
            ->add('billingLastName',TextType::class, ['label' => 'Last Name', 'attr' => ['placeholder' => 'Last Name...']])
            ->add('billingEmail',EmailType::class, ['label' => 'E-mail', 'attr' => ['placeholder' => 'E-mail...']])
            ->add('billingPhone',TextType::class, ['label' => 'Phone', 'attr' => ['placeholder' => 'Phone...']])
            ->add('billingAddress',TextType::class, ['label' => 'Address', 'attr' => ['placeholder' => 'Address...']])
            ->add('billingAddress2',TextType::class, ['label' => 'Address 2', 'attr' => ['placeholder' => 'Address 2...']])
            ->add('billingCity',TextType::class, ['label' => 'City', 'attr' => ['placeholder' => 'City...']])
            ->add('billingState',ChoiceType::class, [
                    'label' => 'State',
                    'choices' => $states,
                    'required' => false,
                    'placeholder' => '- select -',
                    'empty_data' => null
                ]
            )
            ->add('billingZip',IntegerType::class,['label' => 'Zip', 'attr' => ['placeholder' => 'Zip...']])
            ->add('shippingFirstName', TextType::class, ['label' => 'First Name', 'attr' => ['placeholder' => 'First Name...']])
            ->add('shippingLastName',TextType::class, ['label' => 'Last Name', 'attr' => ['placeholder' => 'Last Name...']])
            ->add('shippingEmail',EmailType::class ,['label' => 'E-mail', 'attr' => ['placeholder' => 'E-mail...']])
            ->add('shippingPhone',TextType::class ,['label' => 'Phone', 'attr' => ['placeholder' => 'Phone...']])
            ->add('shippingAddress',TextType::class ,['label' => 'Address', 'attr' => ['placeholder' => 'Address...']])
            ->add('shippingAddress2',TextType::class ,['label' => 'Address 2', 'attr' => ['placeholder' => 'Address 2...']])
            ->add('shippingCity',TextType::class ,['label' => 'City', 'attr' => ['placeholder' => 'City...']])
            ->add('shippingState',ChoiceType::class, [
                    'label' => 'State',
                    'choices' => $states,
                    'required' => false,
                    'placeholder' => '- select -',
                    'empty_data' => null
                ]
            )
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
            ->add('creditCardName', TextType::class, ['label' => 'Name on Card', 'attr' => ['placeholder' => 'Name on Card...']])
            ->add('submit', SubmitType::class, ['label' => 'Place Order']);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\StoreOrder',
            'states' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        // return 'appbundle_storeorder';
        return '';
    }


}

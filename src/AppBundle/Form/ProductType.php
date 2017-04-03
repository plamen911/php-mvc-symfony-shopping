<?php

namespace AppBundle\Form;

use AppBundle\Entity\Photo;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
//            ->add('category', EntityType::class, [
//                'class' => 'AppBundle\Entity\Category',
//                'choice_label' => 'name',
//            ])
            ->add('category', EntityType::class, [
                'class' => 'AppBundle\Entity\Category',
                'query_builder' => function (EntityRepository $er) use ($options) {
                    /**
                     * @var \AppBundle\Entity\Product $product
                     */
                    $product = $options['data'];

                    return $er->createQueryBuilder('c')
                        ->where('c.departmentId = :departmentId')
                        ->setParameter('departmentId', $product->getCategory()->getDepartmentId())
                        ->orderBy('c.position', 'ASC');
                },
                'choice_label' => 'name',
            ])
            ->add('name', TextType::class)
            ->add('isTaxable', CheckboxType::class)
            ->add('price', TextType::class)
            ->add('qty', TextType::class)
            ->add('dimension', ChoiceType::class, array(
                'choices'  => array(
                    '- select -' => null,
                    'lb' => 'lb',
                    'oz' => 'oz',
                    'per person' => 'per person',
                ))
            )
            ->add('availability', ChoiceType::class, array(
                    'choices'  => array(
                        '- product is not available -' => null,
                        'daily' => 'Daily',
                        'weekdays' => 'Week Days',
                        'multidays' => 'Multiple Days',
                    ))
            )
            ->add('availabilityDays', HiddenType::class)
            ->add('description', TextareaType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Product'
        ));
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_product_type';
    }
}

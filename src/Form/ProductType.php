<?php

namespace App\Form;

use App\Entity\Product;

use App\Entity\ProductCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('category',EntityType::class,[
                'class'=>ProductCategory::class,
                'multiple'=> true,
                'autocomplete' => true,
                'choice_label' => 'name',
                'placeholder' => 'Choisissez un nom dans la liste',
            ])
            ->add('price')
            ->add('tax_rate')
            ->add('isRecurring', CheckboxType::class, [
                'label'    => 'Facturation récurrente ?',
                'required' => false,
                'attr' => ['class' => 'custom-control-input'],
                'help' => 'Cochez cette case si le produit doit être facturé de manière récurrente.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}

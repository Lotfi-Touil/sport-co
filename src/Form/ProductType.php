<?php

namespace App\Form;

use App\Entity\Product;

use App\Entity\ProductCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

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
            ->add('images', FileType::class, [
                'label' =>false,
                'multiple' => true,
                'mapped' => false,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}

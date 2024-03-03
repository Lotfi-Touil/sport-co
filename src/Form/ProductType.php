<?php

namespace App\Form;

use App\Entity\Product;

use App\Entity\ProductCategory;
use Doctrine\ORM\EntityRepository;
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
        $companyId = $options['company_id'];

        $builder
            ->add('name')
            ->add('description')
            ->add('category', EntityType::class, [
                'class' => ProductCategory::class,
                'query_builder' => function (EntityRepository $er) use ($companyId) {
                    return $er->createQueryBuilder('pc')
                        ->where('pc.company = :companyId')
                        ->setParameter('companyId', $companyId)
                        ->orderBy('pc.name', 'ASC');
                },
                'multiple' => true,
                'autocomplete' => true,
                'choice_label' => 'name',
                'placeholder' => 'Choisissez une catégorie',
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
            'company_id' => null,
        ]);
    }
}

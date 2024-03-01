<?php

namespace App\Form;

use App\Entity\Invoice;
use App\Entity\InvoiceStatus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('totalAmount')
            ->add('subtotal')
            ->add('notes')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('submittedAt')
            ->add('expiryDate')
            ->add('invoiceStatus', EntityType::class, [
                'class' => InvoiceStatus::class,
'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Invoice::class,
        ]);
    }
}

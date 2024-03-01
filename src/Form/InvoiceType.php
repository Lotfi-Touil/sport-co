<?php

namespace App\Form;

use App\Entity\Invoice;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $status = $options['status_choices'];

        $builder
            ->add('notes')
            ->add('invoiceStatus', ChoiceType::class, [
                'choices' => $status,
                'choice_label' => function($status) {
                    return $status->getTitle();
                },
                'choice_value' => 'id',
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Invoice::class,
            'status_choices' => [],
        ]);
    }
}

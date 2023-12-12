<?php

namespace App\Form;

use App\Constant;
use App\Entity\Quote;
use App\Entity\QuoteStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class QuoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $status = $options['status_choices'];
        $defaultStatus = $options['entityManager']->getRepository(QuoteStatus::class)->find(Constant::ID_QUOTE_STATUS_BROUILLON);

        $builder
            ->add('notes')
            ->add('quoteStatus', ChoiceType::class, [
                'choices' => $status,
                'choice_label' => function($status) {
                    return $status->getTitle(); // ou la propriété que vous voulez afficher
                },
                'choice_value' => 'id',
                'data' => $defaultStatus, // Définir la valeur par défaut
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Quote::class,
            'status_choices' => [],
        ]);

        $resolver->setRequired('entityManager'); // Ajouter cette ligne
    }
}

<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\EmailTemplate;
use App\Entity\EmailType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmailTemplateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', EntityType::class, [
                'class' => EmailType::class,
                'choice_label' => 'type',
                'disabled' => 'true'
            ])
            ->add('subject')
            ->add('body', CKEditorType::class, [
                'config' => [
                    'toolbar' => 'standard',
                ],
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EmailTemplate::class,
        ]);
    }
}

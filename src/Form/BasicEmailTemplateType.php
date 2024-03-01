<?php

namespace App\Form;

use App\Entity\BasicEmailTemplate;
use App\Entity\EmailType;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BasicEmailTemplateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('subjet')
            ->add('body', CKEditorType::class, [
                'config' => [
                    'toolbar' => 'standard', // Vous pouvez choisir une configuration de barre d'outils personnalisée
                ],
            ])
            ->add('type', EntityType::class, [
                'class' => EmailType::class,
                'choice_label' => 'type',
                'query_builder' => function (EntityRepository $er) : QueryBuilder {
                    return $er->createQueryBuilder('e')
                        ->leftJoin('App\Entity\BasicEmailTemplate', 'bet', 'WITH', 'bet.type = e.id')
                        ->where('bet.id IS NULL');
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BasicEmailTemplate::class,
        ]);
    }
}

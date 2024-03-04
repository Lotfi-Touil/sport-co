<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Company;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class UserEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName')
            ->add('lastName')
            ->add('email')
            ->add('phone')
            ->add('address')
            ->add('profilePicture')
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => false, // Rend ce champ optionnel
                'mapped' => false, // Ne mappe pas directement à l'entité
                'first_options'  => ['label' => 'Nouveau mot de passe (laisser vide si inchangé)'],
                'second_options' => ['label' => 'Confirmez le nouveau mot de passe'],
                'invalid_message' => 'Les deux mots de passe doivent correspondre.',
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} charactères',
                        'max' => 4096, // Maximum recommandé par Symfony pour des raisons de sécurité
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

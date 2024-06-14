<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName')
            ->add('lastName')
            ->add('birthdate')
            ->add('address')
            ->add('email')
            ->add('phone')
            ->add('company')
            ->add('workstation');

        if ($options['is_admin']) {
            $builder->add('agent', EntityType::class, [
                'class' => User::class,
                'placeholder' => 'Choose agent',
                'required' => false,
            ]);
            $builder->add('status', null, ['label' => "Is Active"]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
            'is_admin' => false
        ]);
    }
}

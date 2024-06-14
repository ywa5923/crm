<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\County;
use App\Entity\User;
use App\Repository\CountyRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use App\Repository\UserRepository;

class SearchVisitType extends AbstractType
{
    public function __construct(
        private CountyRepository $countyRepository,
        private UserRepository $userRepository
    ) {
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('start', DateType::class)
            ->add('end', DateType::class)
            ->add('agent', EntityType::class, [
                'class' => User::class,
                'placeholder' => '...',
                'choices' => $this->userRepository->getAllAgents(),
                'required' => false,
            ])
            ->add('client', EntityType::class, [
                'class' => Company::class,
                'placeholder' => '...'

            ])
            ->add('county', EntityType::class, [
                'class' => County::class,
                'placeholder' => '...',
                'choice_value' => function ($county) {
                    if (is_string($county)) {
                        return $this->countyRepository->findOneBy(["name" => $county])->getName();
                    }
                    return $county?->getName();
                },
            ])
            ->add('city', TextType::class, [])
            ->add('street', TextType::class, [])
            ->add('completed', CheckboxType::class);;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}

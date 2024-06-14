<?php

namespace App\Form;

use App\Entity\Calendar;
use App\Entity\Client;
use App\Entity\Company;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;

use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CalendarType extends AbstractType
{

    public function __construct(private TokenStorageInterface $token)
    {
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->token->getToken()->getUser();



        $builder
            ->add('client', EntityType::class, [
                'class' => Company::class,
                'placeholder' => 'Choose client',
                'autocomplete' => true,
                'required' => false,
                'query_builder' => function (EntityRepository $er) use ($user): QueryBuilder {
                    return $er->createQueryBuilder('c')
                        ->andWhere('c.agent =:user')
                        ->setParameter('user', $user)
                        ->orderBy('c.name', 'ASC');
                }

            ])
            ->add('title')
            ->add('start')
            ->add('end')
            ->add('description')
            ->add('textColor', ColorType::class)
            ->add('borderColor', ColorType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Calendar::class,
        ]);
    }
}

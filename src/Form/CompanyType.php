<?php

namespace App\Form;

use App\Entity\Adress;
use App\Entity\Company;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class CompanyType extends AbstractType
{

    public function __construct(private UserRepository $userRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 3])
                ]
            ])
            ->add('phone')
            ->add('workstation')
            ->add('image', FileType::class, [
                'label' => 'Image (jpeg,png)',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using attributes
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid jpeg or png file',
                    ])
                ],
            ]);



        $builder->add('owner', Client2Type::class)
            ->add('registrationAddress', AdressType::class)
            ->add('workstationAddress', AdressType::class);


        if ($options['is_admin']) {
            $builder->add('agent', EntityType::class, [
                'class' => User::class,
                'choices' => $this->userRepository->getAllAgents(),
                'placeholder' => 'Choose agent',
                'required' => false,
            ]);
        }
        $builder->add('active');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
            'is_admin' => false,
            'is_edit' => false
        ]);
    }
}

//http://freedelta.free.fr/r/learn-symfony/how-to-embed-nested-forms-in-symfony/index.php
<?php

namespace App\Form;

use App\Entity\Adress;
use App\Entity\County;
use App\Repository\CountyRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\CallbackTransformer;

class AdressType extends AbstractType
{
    public function __construct(private CountyRepository $countyRepository)
    {
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type')
            ->add('street')
            ->add('number')
            ->add('line1')
            ->add('county', EntityType::class, [
                'class' => County::class,
                'choice_value' => function ($county) {


                    if (is_string($county)) {
                        return $this->countyRepository->findOneBy(["name" => $county])->getAuto();
                    }
                    return $county?->getAuto();
                },
            ]);

        // $builder->get('county')->addModelTransformer(new CallbackTransformer(
        //     function ($county): string {
        //         // transform the array to a string
        //         $county = $this->countyRepository->findOneBy(["name" => $county]);
        //         return $county ?? "aa";
        //     },
        //     function ($tagsAsString): string {

        //         // transform the string back to an array
        //         // return explode(', ', $tagsAsString);
        //         return $tagsAsString;
        //     }
        // ));

        $builder->add('city')
            ->add('zipCode');
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Adress::class,
        ]);
    }
}

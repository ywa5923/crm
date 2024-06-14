<?php

namespace App\Controller;

use App\Entity\County;
use App\Entity\Locality;
use App\Repository\LocalityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;



class LocalityController extends AbstractController
{
    public function getLocality()
    {
    }

    public function getCounties()
    {
    }

    #[Route('load_counties', name: "load_counties")]
    public function loadCounties(EntityManagerInterface $em)
    {
        $localities = $em->getRepository(Locality::class)->getCounties();


        foreach ($localities as $loc) {
            // $county = new County();
            // $county->setName($loc->getCounty());
            // $county->setAuto($loc->getAuto());
            // $em->persist($county);
            // $em->flush();


        }

        dd($localities);
    }
}

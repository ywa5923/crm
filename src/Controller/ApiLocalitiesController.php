<?php

namespace App\Controller;

use App\Entity\Locality;
use App\Repository\LocalityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApiLocalitiesController extends AbstractController
{
    #[Route('/api/localities', name: 'app_api_localities')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {

        $searchTerm = $request->query->get("search");
        $relatedSearchTerm = $request->query->get("related");

        $localities = $em->getRepository(Locality::class)->search($searchTerm, $relatedSearchTerm);

        return $this->json($localities);
    }
}

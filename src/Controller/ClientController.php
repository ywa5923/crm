<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/client')]
class ClientController extends AbstractController
{
    #[Route('/list', name: 'app_client_list', methods: ['GET', 'POST'])]
    public function index(ClientRepository $clientRepository, Request $request): Response
    {
        // if ($this->isGranted('ROLE_ADMIN')) {
        //     $clients = $clientRepository->findAll();
        // } else {
        //     $clients = $clientRepository->findBy(["agent" => $this->getUser()]);
        // }

        if ($request->query->get("isSearch")) {

            $searchTerm = $request->query->get("search_term");
            $searchField = $request->query->get("search_field");
            $qb = $clientRepository->search($searchTerm, $searchField);
        } else {
            $qb = $clientRepository->getFindAllQueryBuilder();
        }


        $pagerfanta = new Pagerfanta(new QueryAdapter($qb));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->query->get('page', 1));
        return $this->render('client/index.html.twig', [
            //'clients' => $clients,
            'pager' => $pagerfanta
        ]);
    }

    #[Route('/new', name: 'app_client_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $client = new Client();
        $is_admin = $this->isAdmin();
        $form = $this->createForm(ClientType::class, $client, ['is_admin' => $this->isAdmin()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //only admin can add clients without agent_id
            if (!$is_admin && is_null($client->getAgent())) {
                $client->setAgent($this->getUser());
            }
            $entityManager->persist($client);
            $entityManager->flush();
            return $this->redirectToRoute('app_client_list', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('client/new.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_client_show', methods: ['GET'])]
    #[IsGranted('CLIENT_VIEW', 'client', 'Accessed denied by security voters', 404)]
    public function show(Client $client): Response
    {
        return $this->render('client/show.html.twig', [
            'client' => $client,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_client_edit', methods: ['GET', 'POST'])]
    #[IsGranted('CLIENT_EDIT', 'client', 'Accessed denied by security voters', 404)]
    public function edit(Request $request, Client $client, EntityManagerInterface $entityManager): Response
    {

        $form = $this->createForm(ClientType::class, $client, [
            "is_admin" => $this->isAdmin()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_client_list', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('client/edit.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_client_delete', methods: ['POST'])]
    #[IsGranted('CLIENT_EDIT', 'client', 'Access denied by security voters', 404)]
    public function delete(Request $request, Client $client, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $client->getId(), $request->request->get('_token'))) {
            $entityManager->remove($client);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_client_list', [], Response::HTTP_SEE_OTHER);
    }

    public function isAdmin()
    {
        return (in_array('ROLE_ADMIN', $this->getUser()->getRoles())) ? true : false;
    }
}

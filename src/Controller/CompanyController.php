<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\User;
use App\Form\Company1Type;
use App\Form\CompanyType;
use App\Repository\CalendarRepository;
use App\Repository\CompanyRepository;
use App\Repository\CountyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/company')]
class CompanyController extends AbstractController
{
    #[Route('/', name: 'app_company_index', methods: ['GET'])]
    public function index(CompanyRepository $companyRepository, CountyRepository $countyRep, Request $request): Response
    {

        // dd($countyRep->findAll());

        if ($request->query->get("isSearch")) {

            $agent = ($this->isGranted('ROLE_ADMIN') ? null : $this->getUser());
            $searchTerm = $request->query->get("search_term");
            $searchField = $request->query->get("search_field");
            $county = $request->query->get("county");
            $city = $request->query->get("city");
            $street = $request->query->get("street");

            $qb = $companyRepository->search($searchField, $searchTerm, $county, $city, $street, $agent);
        } else {
            $qb = $companyRepository->getFindAllQueryBuilder();
        }


        $pagerfanta = new Pagerfanta(new QueryAdapter($qb));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->query->get('page', 1));


        return $this->render('company/index.html.twig', [
            //'companies' => $companyRepository->findAll(),
            'pager' => $pagerfanta,
            'counties' => $countyRep->findAll()

        ]);
    }

    #[Route('/new', name: 'app_company_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $company = new Company();
        $is_admin = $this->isAdmin();
        $form = $this->createForm(CompanyType::class, $company, ['is_admin' => $is_admin]);
        $form->handleRequest($request);
        $this->checkUniqueEmail($company, $form, $entityManager);
        $randPass = md5(rand());
        $company->getOwner()?->setPassword($randPass);
        $company->getOwner()?->setRoles(["ROLE_CLIENT"]);

        if ($form->isSubmitted() && $form->isValid()) {


            //only admin can add clients without agent_id
            if (!$is_admin && is_null($company->getAgent())) {
                $company->setAgent($this->getUser());
            }

            $this->uploadCompanyImage($form, $company, $slugger);
            $entityManager->persist($company);
            $entityManager->flush();
            $this->addFlash('success', 'The client has been saved successfully');
            return $this->redirectToRoute('app_company_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('company/new.html.twig', [
            'company' => $company,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_company_show', methods: ['GET'])]
    #[IsGranted('CLIENT_VIEW', 'company', 'Accessed denied by security voters', 404)]
    public function show(Company $company, CalendarRepository $calendarRepository, Request $request): Response
    {
        //$visits = $company->getVisits();

        $qb = $calendarRepository->getCompanyVisitsQB($company->getId());
        $pagerfanta = new Pagerfanta(new QueryAdapter($qb));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->query->get('page', 1));

        return $this->render('company/show.html.twig', [
            'company' => $company,
            'visits' => $pagerfanta
        ]);
    }

    #[Route('/{id}/edit', name: 'app_company_edit', methods: ['GET', 'POST'])]
    #[IsGranted('CLIENT_EDIT', 'company', 'Accessed denied by security voters', 404)]
    public function edit(Request $request, Company $company, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $is_admin = $this->isAdmin();
        $form = $this->createForm(CompanyType::class, $company, ['is_admin' => $is_admin, "is_edit" => true]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $this->uploadCompanyImage($form, $company, $slugger);
            $entityManager->flush();
            $this->addFlash('success', 'The client has been updated successfully');
            return $this->redirectToRoute('app_company_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('company/edit.html.twig', [
            'company' => $company,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_company_delete', methods: ['POST'])]
    #[IsGranted('CLIENT_EDIT', 'company', 'Access denied by security voters', 404)]
    public function delete(Request $request, Company $company, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $company->getId(), $request->request->get('_token'))) {
            $entityManager->remove($company);
            $entityManager->flush();
            $this->addFlash('success', 'The client has been deleted successfully');
        }

        return $this->redirectToRoute('app_company_index', [], Response::HTTP_SEE_OTHER);
    }

    public function isAdmin()
    {
        return (in_array('ROLE_ADMIN', $this->getUser()->getRoles())) ? true : false;
    }

    public function checkUniqueEmail($company, $form, $entityManager)
    {
        $userEmail = $company->getOwner()?->getEmail();

        $userExists = $entityManager->getRepository(User::class)->findOneBy(["email" => $userEmail]);

        if ($userExists) {
            $form->get("owner")->get("email")->addError(new FormError("This email already exists"));
        }
    }

    public function uploadCompanyImage($form, &$entity, $slugger)
    {
        $brochureFile = $form->get('image')->getData();

        if ($brochureFile) {
            $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $brochureFile->guessExtension();

            // Move the file to the directory where brochures are stored
            try {
                $projectDir = $this->getParameter('kernel.project_dir');
                $brochuresDirectory = $projectDir . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'images';
                $brochureFile->move($brochuresDirectory, $newFilename);
            } catch (FileException $e) {
                throw $e;
                // ... handle exception if something happens during file upload
            }
            $entity->setPhotoFileName($newFilename);
        }
    }
}

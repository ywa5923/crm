<?php

namespace App\Controller;

use App\Entity\Calendar;
use App\Form\CalendarType;
use App\Repository\CalendarRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints\Length;
use App\Form\SearchVisitType;

use Symfony\Component\Validator\Constraints\NotBlank;

#[Route('/events', name: "app_events_")]
class EventController extends AbstractController
{
    #[Route('/', name: 'list', methods: ['GET'])]
    public function index(CalendarRepository $calendarRepository, Request $request): Response
    {
        $searchForm = $this->createForm(SearchVisitType::class);
        $user = ($this->isGranted('ROLE_ADMIN')) ? null : $this->getUser();
        if ($request->query->get("isSearch")) {


            $formData = $request->query->all()[$searchForm->getName()];
            $start =  $formData['start'];
            $end = $formData['end'];
            $client = $formData['client'];
            $county = $formData['county'];
            $city = $formData['city'];
            $street = $formData['street'];
            $agent = $formData['agent'] ?? $user;
            $completed = $formData['completed'] ?? null;


            $qb = $calendarRepository->search2($start, $end, $agent, $client, $county, $city, $street, $completed);
            //  dd($qb->getQuery()->getResult());
            //dd($qb->getQuery()->getSql());
            // dd($qb->getQuery()->getParameters());

            // $start = $request->query->get("start");
            // $end = $request->query->get("end");
            // $agent = trim($request->query->get("agent"));
            // $completed = $request->query->get("completed");

            //$qb = $calendarRepository->search($start, $end, $agent, $completed);
            // return $this->render('event/index.html.twig', [
            //     'isSearch' => true,
            //     'pager' => $events
            // ]);
        } else {
            $user = ($this->isGranted('ROLE_ADMIN')) ? null : $this->getUser();
            $qb = $calendarRepository->getFindAllQueryBuilder($user);
        }

        $pagerfanta = new Pagerfanta(new QueryAdapter($qb));
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->setCurrentPage($request->query->get('page', 1));
        // if ($this->isGranted('ROLE_ADMIN')) {
        //    // $events = $calendarRepository->findAll();
        //    $qb=$calendarRepository->getFindAllQueryBuilder()
        // } else {
        //     $events = $calendarRepository->findBy(["user" => $this->getUser()]);
        // }
        return $this->render('event/index.html.twig', [
            //'calendars' => $events
            'pager' => $pagerfanta,
            'searchForm' => $searchForm->createView()

        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $calendar = new Calendar();
        $start = $request->query->get("start");
        $end = $request->query->get("end");
        $calendar->setUser($this->getUser());

        if (isset($start) && isset($end)) {
            $eventStart = \DateTime::createFromFormat('Y/m/d H:i',  $start);
            $eventEnd = \DateTime::createFromFormat('Y/m/d H:i',   $end);

            $calendar->setStart($eventStart);
            $calendar->setEnd($eventEnd);
        }

        $form = $this->createForm(CalendarType::class, $calendar);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($calendar);
            $entityManager->flush();

            return $this->redirectToRoute('app_events_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/new.html.twig', [
            'calendar' => $calendar,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/complete', name: 'complete', methods: ['GET', 'POST'])]
    public function complete(Calendar $calendar, Request $request, EntityManagerInterface $em)
    {
        $defaultData = ['message' => $calendar->getObservations()];
        $form = $this->createFormBuilder($defaultData)
            ->add('message', TextareaType::class, [
                'constraints' => [
                    new Length(['min' => 3, 'max' => 3000]), new NotBlank()
                ]
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // data is an array with "name", "email", and "message" keys
            $data = $form->getData();
            $calendar->setIsComplete(true);
            $calendar->setObservations($data['message']);
            $em->flush();
            $em->persist($calendar);
            $this->addFlash('success', 'The visit has been completed successfully');
            return $this->redirectToRoute('app_events_new');
            //return $this->redirectToRoute('app_events_show', ['id' => $request->attributes->get("id")]);
        }

        return $this->render('event/complete.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[IsGranted('CAL_VIEW', 'calendar', 'Event not found', 404)]
    public function show(Calendar $calendar, Request $request): Response
    {
        return $this->render('event/show.html.twig', [
            'calendar' => $calendar,
            'referer' => $request->headers->get('referer')
        ]);
    }


    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    #[IsGranted('CAL_EDIT', 'calendar', 'Event not found', 404)]
    public function edit(Request $request, Calendar $calendar, EntityManagerInterface $entityManager): Response
    {

        $form = $this->createForm(CalendarType::class, $calendar);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $route = $request->headers->get('referer');

            if ($referer = $request->query->get('referer')) {
                return $this->redirect($referer);
            } else {
                return $this->redirectToRoute('app_calendar', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('event/edit.html.twig', [
            'calendar' => $calendar,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    #[IsGranted('CAL_EDIT', 'calendar', 'Event not found', 404)]
    public function delete(Request $request, Calendar $calendar, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $calendar->getId(), $request->request->get('_token'))) {
            $entityManager->remove($calendar);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_events_list', [], Response::HTTP_SEE_OTHER);
    }
}

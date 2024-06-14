<?php

namespace App\Controller;

use App\Entity\Calendar;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CalendarController extends AbstractController
{
    #[Route('/calendar', name: 'app_calendar')]
    public function index(EntityManagerInterface $em, Request $request): Response
    {

        // /**  @var User $user  */
        // $user = $this->getUser();

        // $calendarColor = $user->getCalendarColor();
        // if ($this->isGranted('ROLE_ADMIN')) {
        //     $events = $em->getRepository(Calendar::class)->findAll();
        // } else {
        //     $events = $em->getRepository(Calendar::class)->findBy(["user" => $user]);
        // }


        // $calendarData = [];
        // foreach ($events as $event) {
        //     $calendarData[] = [
        //         'id' => $event->getId(),
        //         'title' => $event->getTitle(),
        //         'description' => $event->getDescription(),
        //         'start' => $event->getStart()->format('Y-m-d H:i:s'),
        //         'end' => $event->getEnd()->format('Y-m-d H:i:s'),
        //         'backgroundColor' => $user->getCalendarColor(),
        //         'borderColor' => $event->getBorderColor(),
        //         'textColor' => $event->getTextColor()
        //     ];
        // }

        // $data = json_encode($calendarData);
        // compact('data')

        $selectedAgents = json_decode(urldecode($request->query->get("agents")));



        if ($this->isGranted('ROLE_ADMIN')) {
            $agents = $em->getRepository(User::class)->getAllAgents();
        } else {
            $agents = [];
        }
        return $this->render('calendar/index.html.twig', ["agents" => $agents, "selectedAgents" => $selectedAgents]);
    }

    #[Route('/calendar/data/{id}', name: 'app_calendar_data', defaults: ['id' => null], methods: ['GET'])]
    public function getData(EntityManagerInterface $em, Request $request, ?User $user): JsonResponse
    {
        $start = $request->query->get("start");
        $end = $request->query->get("end");
        //'Y-m-d H:i:s 
        $eventStart = \DateTime::createFromFormat(DateTimeInterface::RFC3339,  $start);
        $eventEnd = \DateTime::createFromFormat(DateTimeInterface::RFC3339,   $end);
        //date("Y-m-d H:i:s", strtotime($start)

        /**  @var User $user  */
        // $user = $this->getUser();


        // $data = $em->getRepository(Calendar::class)->findIntervalData($eventStart, $eventEnd, null);

        $events = $em->getRepository(Calendar::class)->findIntervalData($eventStart, $eventEnd, $user);

        // if ($this->isGranted('ROLE_ADMIN')) {
        //     $events = $em->getRepository(Calendar::class)->findIntervalData($eventStart, $eventEnd, null);
        // } else {
        //     $events = $em->getRepository(Calendar::class)->findIntervalData($eventStart, $eventEnd, $user);
        // }



        $calendarData = [];
        foreach ($events as $event) {
            // dd($event->getClient());
            $data = [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'description' => $event->getDescription(),
                'agent' => $event->getUser()->getFirstName() . " " . $event->getUser()->getLastName(),

                'start' => $event->getStart()->format('Y-m-d H:i:s'),
                'end' => $event->getEnd()->format('Y-m-d H:i:s'),
                'backgroundColor' => $event->getUser()->getCalendarColor(),
                'borderColor' => $event->getBorderColor(),
                'textColor' => $event->getTextColor()
            ];
            if ($event->getClient()) {
                $data["client"] = $event->getClient();
            }
            $calendarData[] = $data;
        }

        //$data = json_encode($calendarData);
        return new JsonResponse($calendarData);
    }
}

<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\AppLoginAuthenticator;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

#[Route('/admin/users', name: 'users_')]
class UserController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/change-password/{id}', name: 'change_password')]
    public function changePassword(User $user, Request $request,  UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $em)
    {
        $user = new User();
        $form = $this->createForm(ChangePasswordFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'The password saved successfully');
        }

        return $this->render('user/change-password.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/register', name: 'register')]
    public function register(Request $request,  UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, AppLoginAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {

        // if ($this->getUser()) {
        //     return $this->redirectToRoute('app_admin');
        // }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setRoles(["ROLE_AGENT"]);
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );


            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'The agent was saved successfully');
            //generate a signed url and email it to the user
            // $this->emailVerifier->sendEmailConfirmation(
            //     'app_verify_email',
            //     $user,
            //     (new TemplatedEmail())
            //         ->from(new Address('from@example.com', 'Admin'))
            //         ->to($user->getEmail())
            //         ->subject('Please Confirm your Email')
            //         ->htmlTemplate('registration/confirmation_email.html.twig')
            // );
            //do anything else you need here, like send an email

            //crm@beconcept.ro

            // return $userAuthenticator->authenticateUser(
            //     $user,
            //     $authenticator,
            //     $request
            // );


            return $this->redirectToRoute('users_list');
        }


        return $this->render('user/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
    #[Route('/edit/{id}', name: 'edit')]
    public function editUser(User $user, Request $request, EntityManagerInterface $em)
    {

        //admin cannot be modified or updated
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->addFlash('error', 'The admin cannot be edited');
            return $this->redirectToRoute('users_list');
        }

        $form = $this->createForm(RegistrationFormType::class, $user, ['is_edit' => true]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'The agent was saved successfully');
            return $this->redirectToRoute('users_list');
        }

        return $this->render('user/register.html.twig', [
            'registrationForm' => $form,
            'isEdit' => true
        ]);
    }

    #[Route('/delete/{id}', name: 'delete', requirements: ['id' => '\d+'])]
    public function deleteUser(EntityManagerInterface $em, User $user)
    {
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->addFlash('error', 'The admin cannot be deleted');
            return $this->redirectToRoute('users_list');
        }


        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Agent has been deleted successfully');
        return $this->redirectToRoute('users_list');
    }

    #[Route('/list', name: 'list')]
    public function listAgents(EntityManagerInterface $em)
    {
        // $agents = $em->getRepository(User::class)->findAll();
        $agents = $em->getRepository(User::class)->getAllAgents();

        return $this->render('user/list.html.twig', ["agents" => $agents]);
    }

    ##[Route('/verify/email', name: 'verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_admin');
    }
}

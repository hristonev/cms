<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\ChangePasswordForm;
use AppBundle\Form\EditPasswordForm;
use AppBundle\Form\Model\ChangePassword;
use AppBundle\Form\RestorePasswordForm;
use AppBundle\Form\UserEditForm;
use AppBundle\Form\UserRegistrationForm;
use AppBundle\Service\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    /**
     * @Route("/register", name="user_register")
     * @param Request $request
     * @return null|\Symfony\Component\HttpFoundation\Response
     */
    public function registerAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $referrerUser = $em->getRepository('AppBundle:User')
            ->findOneBy([
                'email' => $this->get('session')->get('referrerUser')
            ])
        ;

        $form = $this->createForm(UserRegistrationForm::class);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $password = Security::getRandomPassword();
            /** @var User $user **/
            $user = $form->getData();
            $user->setPlainPassword($password);
            $user->setReferFromUser($referrerUser);

            $em->persist($user);
            $em->flush();

            $mail = $this->get('app.service.mail');
            $mail->registration(
                $user->getEmail(),
                $user->getName(),
                $password
            );

            $this->addFlash('success', $this->get('translator')->trans('msg.success.register', [
                'user' => $user->getEmail()
            ]));

            return $this->get('security.authentication.guard_handler')
                ->authenticateUserAndHandleSuccess(
                    $user,
                    $request,
                    $this->get('app.security.login_form_authenticator'),
                    'main'
                );
        }

        return $this->render('user/register.html.twig', [
            'form' => $form->createView(),
            'referrerUser' => $referrerUser
        ]);
    }

    /**
     * @Route("/reset_account_password", name="reset_password")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function resetPasswordsAction(Request $request)
    {
        $configExpireToken = $this->getParameter('restore_token_expire');
        $form = $this->createForm(RestorePasswordForm::class);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $email = $form->get('email')->getData();
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('AppBundle:User')->findOneBy([
                'email' => $email
            ]);
            if($user){
                $secret = random_bytes(32);
                $token = hash('sha256', bin2hex($secret));

                $expire = new \DateTime('now'); //current date/time
                $expire->add(new \DateInterval("PT{$configExpireToken}H"));

                $user->setRestoreToken($token);
                $user->setRestoreExpire($expire);
                $em->persist($user);
                $em->flush();

                $message = \Swift_Message::newInstance()
                    ->setSubject($this->container->get('translator')->trans('password_recovery_mail', [], 'email'))
                    ->setFrom($this->container->getParameter('mailer_system_email'))
                    ->setTo($email)
                    ->setBody(
                        $this->container->get('templating')->render(
                            'emails/reset_passowrd.html.twig',
                            [
                                'token' => base64_encode($token),
                                'valid_time' => $configExpireToken
                            ]
                        ),
                        'text/html'
                    );

                $send = $this->container->get('mailer')->send($message);

                $this->addFlash('success', $this->get('translator')->trans('msg.success.reset_account'));
            }

            return $this->redirectToRoute('homepage');
        }

        return $this->render('security/reset_password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/reset_account_password/{token}", name="restore_password")
     * @param string $token
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function restorePasswordAction($token, Request $request)
    {
        // Set first access date
        $em = $this->getDoctrine()->getManager();
        /** @var User $user */
        $user = $em->getRepository('AppBundle:User')->getUserByRestoreToken(base64_decode($token));

        if(!$user){
            $this->addFlash('error', $this->get('translator')->trans('msg.invalid_reset_token'));
            return $this->redirectToRoute('homepage');
        }

        $form = $this->createForm(ChangePasswordForm::class);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $user->setPlainPassword($form->get('password')->getData());
            $user->setRestoreToken(null);
            $user->setRestoreExpire(null);
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', $this->get('translator')->trans('msg.success.change_password'));

            return $this->redirectToRoute('security_login');
        }

        return $this->render('security/change_password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/register/{code}", name="user_register_referral")
     * @param string $code
     * @return null|\Symfony\Component\HttpFoundation\Response
     */
    public function registerReferralAction($code)
    {
        // TODO will be slow for more users in DB. Save hash on user create!
        /** @var User $referrerUser */
        $referrerUser = $this->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
            ->getReferUser($code)
        ;
        $this->get('session')->set('referrerUser', $referrerUser->getEmail());
        return $this->redirectToRoute('user_register');
    }

    /**
     * @Route("/edit/{email}", name="user_edit")
     * @param string $email
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction($email, Request $request)
    {
        $form = $this->createForm(UserEditForm::class, $this->getUser());

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $this->addFlash('success', $this->get('translator')->trans('msg.success.edit_user_settings', [
                'user' => $user->getEmail()
            ]));
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'email' => $email
        ]);
    }

    /**
     * @Route("/edit/{email}/password", name="user_edit_password")
     * @param string $email
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editPasswordAction($email, Request $request)
    {
        $changePasswordModel = new ChangePassword();
        $form = $this->createForm(EditPasswordForm::class, $changePasswordModel);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $this->addFlash('success', $this->get('translator')->trans('msg.success.change_password'));

            /** @var User $user */
            $user = $this->getUser();
            $user->setPlainPassword($form->get('newPassword')->getData());
            $user->setRestoreToken(null);
            $user->setRestoreExpire(null);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('user_edit', [
                'email' => $email
            ]);
        }

        return $this->render('security/edit_password.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
<?php

namespace AppBundle\Security;

use AppBundle\Entity\User;
use KnpU\OAuth2ClientBundle\Client\OAuth2Client;
use KnpU\OAuth2ClientBundle\Client\Provider\LinkedInClient;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use Doctrine\ORM\EntityManager;
use League\OAuth2\Client\Provider\FacebookUser;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use KnpU\OAuth2ClientBundle\Client\Provider\FacebookClient;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LinkedInAuthenticator extends SocialAuthenticator
{
    use TargetPathTrait;

    private $clientRegistry;
    private $em;
    private $router;
    private $container;

    public function __construct(ClientRegistry $clientRegistry, EntityManager $em, RouterInterface $router, ContainerInterface $container)
    {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
        $this->router = $router;
        $this->container = $container;
    }

    public function getCredentials(Request $request)
    {

        if (strpos($request->getPathInfo(), '/connect/linkedin/check') === false) {
            // don't auth
            return null;
        }

        return $this->fetchAccessToken($this->getLinkedInClient());
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        /** @var FacebookUser $linkedInUser */
        $linkedInUser = $this->getLinkedInClient()
            ->fetchUserFromToken($credentials);

        $email = $linkedInUser->getEmail();
        $name = $linkedInUser->getFirstName(). ' '. $linkedInUser->getLastName();

        // 1) have they logged in with LinkedIn before? Easy!
        $existingUser = $this->em->getRepository('AppBundle:User')
            ->findOneBy(['linkedInId' => $linkedInUser->getId()]);
        if ($existingUser) {
            return $existingUser;
        }

        // 2) do we have a matching user by email?
        $user = $this->em->getRepository('AppBundle:User')
            ->findOneBy(['email' => $email]);

        if(is_null($user)){
            $password = base64_encode(random_bytes(10));

            $user = new User();
            $user->setEmail($email);
            $user->setName($name);
            $user->setPlainPassword($password);

            $message = \Swift_Message::newInstance()
                ->setSubject($this->container->get('translator')->trans('registerViaOauth', [], 'email'))
                ->setFrom($this->container->getParameter('mailer_system_email'))
                ->setTo($email)
                ->setBody(
                    $this->container->get('templating')->render(
                        'emails/registration_facebook.html.twig',
                        [
                            'name' => $name,
                            'password' => $password
                        ]
                    ),
                    'text/html'
                );
            $this->container->get('mailer')->send($message);
        }
        // 3) Maybe you just want to "register" them by creating
        // a User object
        $user->setLinkedInId($linkedInUser->getId());
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    /**
     * @return OAuth2Client
     */
    private function getLinkedInClient()
    {
        return $this->clientRegistry
            // "linkedin_main" is the key used in config.yml
            ->getClient('linkedin_main');
    }

    // ...
    public function start(Request $request, AuthenticationException $authException = null)
    {
        // TODO: Implement start() method.
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        // TODO: Implement onAuthenticationFailure() method.
    }

    protected function getDefaultSuccessRedirectUrl()
    {
        return $this->router->generate('homepage');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $request->getSession()
            ->getFlashBag()
            ->add('success', $this->container->get('translator')->trans('application.login_success', [
                '%platform%' => 'LinkedIn'
            ]));

        if (!method_exists($this, 'getDefaultSuccessRedirectUrl')) {
            throw new \Exception(sprintf('You must implement onAuthenticationSuccess() or getDefaultSuccessRedirectUrl() in %s.', get_class($this)));
        }

        $targetPath = null;

        // if the user hit a secure page and start() was called, this was
        // the URL they were on, and probably where you want to redirect to
        if ($request->getSession() instanceof SessionInterface) {
            $targetPath = $this->getTargetPath($request->getSession(), $providerKey);
        }

        if (!$targetPath) {
            $targetPath = $this->getDefaultSuccessRedirectUrl();
        }

        return new RedirectResponse($targetPath);
    }
}
<?php

namespace App\Security;

use App\Model\Flusher;
use App\Model\Manager\Entity\Auth\ManagerAuth;
use App\Model\Manager\Entity\Auth\ManagerAuthRepository;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\Manager\Service\PasswordHasher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator implements PasswordAuthenticatedInterface
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private $entityManager;
    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordHasher;
    /**
     * @var ManagerAuthRepository
     */
    private ManagerAuthRepository $managerAuthRepository;
    /**
     * @var Flusher
     */
    private Flusher $flusher;
    /**
     * @var ManagerRepository
     */
    private ManagerRepository $managerRepository;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, PasswordHasher $passwordHasher, ManagerRepository $managerRepository, ManagerAuthRepository $managerAuthRepository, Flusher $flusher)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordHasher = $passwordHasher;
        $this->managerAuthRepository = $managerAuthRepository;
        $this->flusher = $flusher;
        $this->managerRepository = $managerRepository;
    }

    public function supports(Request $request)
    {
        return self::LOGIN_ROUTE === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'login' => $request->request->get('login'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['login']
        );

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $user = $userProvider->loadUserByUsername($credentials['login']);

//        $user = $this->entityManager->getRepository(Manager::class)->findOneBy(['login' => $credentials['login']]);

        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Менеджер не найден.');
        }

        if (!$user->isActive()) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Аккаунт не активен, обратитесь к администратору.');
        }

        if (!$user->isManager()) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Вы не являетесь менеджером, обратитесь к администратору.');
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->passwordHasher->validate($credentials['password'], $user->getPassword());
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function getPassword($credentials): ?string
    {
        return $credentials['password'];
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        $managerAuth = new ManagerAuth($this->managerRepository->get($token->getUser()->getId()), $request->getClientIps()[0], ManagerAuth::TYPE_ENTER);
        $this->managerAuthRepository->add($managerAuth);
        $this->flusher->flush();

        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('home'));
//        throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }

    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}

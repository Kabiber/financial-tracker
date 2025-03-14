<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AppCustomAuthenticator extends AbstractAuthenticator
{
    use TargetPathTrait;

    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function supports(Request $request): ?bool
    {
        return $request->isMethod('POST') && $request->attributes->get('_route') === 'app_login';
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');
        $password = $request->request->get('password', '');

        $this->saveLastUsername($request, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Проверяем сохранённый целевой путь
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            $this->removeTargetPath($request->getSession(), $firewallName);
            return new RedirectResponse($targetPath);
        }

        // Если целевого пути нет, перенаправляем на /dashboard
        return new RedirectResponse($this->urlGenerator->generate('dashboard'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->set('last_username', $request->request->get('email'));

        return null; // Ошибка будет обработана в контроллере
    }

    private function saveLastUsername(Request $request, string $email): void
    {
        $request->getSession()->set('last_username', $email);
    }
}
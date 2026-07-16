<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\User\GoogleAccountLinker;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Token\AccessToken;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class GoogleOAuthController
{
    private const string STATE_COOKIE = 'oauth_google_state';
    private const int STATE_COOKIE_TTL = 600;
    private const string STATE_COOKIE_PATH = '/api/oauth/google';

    private Google $provider;

    public function __construct(
        string $clientId,
        #[\SensitiveParameter]
        string $clientSecret,
        string $redirectUri,
        private string $frontUrl,
        private GoogleAccountLinker $googleAccountLinker,
        private JWTTokenManagerInterface $jwtManager,
        private RefreshTokenGeneratorInterface $refreshTokenGenerator,
        private RefreshTokenManagerInterface $refreshTokenManager,
        private int $refreshTokenTtl,
    ) {
        foreach ([
            'GOOGLE_OAUTH_CLIENT_ID' => $clientId,
            'GOOGLE_OAUTH_CLIENT_SECRET' => $clientSecret,
            'GOOGLE_OAUTH_REDIRECT_URI' => $redirectUri,
            'GOOGLE_OAUTH_FRONT_URL' => $frontUrl,
        ] as $envName => $envValue) {
            if ('' === $envValue) {
                throw new \RuntimeException(\sprintf('La variable d\'environnement "%s" n\'est pas configurée.', $envName));
            }
        }

        $this->provider = new Google([
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'redirectUri' => $redirectUri,
        ]);
    }

    #[Route('/api/oauth/google/redirect', name: 'oauth_google_redirect', methods: ['GET'])]
    public function redirect(Request $request): RedirectResponse
    {
        $authorizationUrl = $this->provider->getAuthorizationUrl([
            'scope' => ['openid', 'email', 'profile'],
        ]);

        $response = new RedirectResponse($authorizationUrl);
        $response->headers->setCookie(
            Cookie::create(self::STATE_COOKIE, $this->provider->getState())
                ->withHttpOnly(true)
                ->withSecure($request->isSecure())
                ->withSameSite(Cookie::SAMESITE_LAX)
                ->withPath(self::STATE_COOKIE_PATH)
                ->withExpires(time() + self::STATE_COOKIE_TTL),
        );

        return $response;
    }

    #[Route('/api/oauth/google/callback', name: 'oauth_google_callback', methods: ['GET'])]
    public function callback(Request $request): RedirectResponse
    {
        if ($request->query->has('error')) {
            return $this->redirectToFrontWithError('oauth_cancelled');
        }

        $state = $request->query->get('state');
        $storedState = $request->cookies->get(self::STATE_COOKIE);

        if (null === $state || null === $storedState || !hash_equals($storedState, $state)) {
            return $this->redirectToFrontWithError('invalid_state');
        }

        $code = $request->query->get('code', '');

        try {
            $accessToken = $this->provider->getAccessToken('authorization_code', ['code' => $code]);
            \assert($accessToken instanceof AccessToken, 'zebi');
            $googleUser = $this->provider->getResourceOwner($accessToken);
        } catch (IdentityProviderException) {
            return $this->redirectToFrontWithError('oauth_failed');
        }

        $data = $googleUser->toArray();
        $email = (string) ($data['email'] ?? '');
        $rawEmailVerified = $data['email_verified'] ?? false;
        $emailVerified = \is_bool($rawEmailVerified) ? $rawEmailVerified : filter_var($rawEmailVerified, FILTER_VALIDATE_BOOL);

        if ('' === $email) {
            return $this->redirectToFrontWithError('oauth_failed');
        }

        $user = $this->googleAccountLinker->findOrCreate(
            googleId: (string) $googleUser->getId(),
            email: $email,
            emailVerified: $emailVerified,
            name: (string) ($data['name'] ?? $email),
        );

        $jwt = $this->jwtManager->create($user);

        $refreshToken = $this->refreshTokenGenerator->createForUserWithTtl($user, $this->refreshTokenTtl);
        $this->refreshTokenManager->save($refreshToken);

        $response = new RedirectResponse(\sprintf('%s#token=%s&refresh_token=%s', $this->frontUrl, $jwt, $refreshToken->getRefreshToken()));
        $response->headers->clearCookie(self::STATE_COOKIE, self::STATE_COOKIE_PATH);

        return $response;
    }

    private function redirectToFrontWithError(string $error): RedirectResponse
    {
        $response = new RedirectResponse($this->frontUrl.'?error='.$error);
        $response->headers->clearCookie(self::STATE_COOKIE, self::STATE_COOKIE_PATH);

        return $response;
    }
}

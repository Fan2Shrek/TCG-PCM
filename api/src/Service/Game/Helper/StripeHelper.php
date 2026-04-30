<?php

declare(strict_types=1);

namespace App\Service\Game\Helper;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class StripeHelper
{
    private const STRIPE_API_URL = 'https://api.stripe.com/v1/checkout/sessions';

    private HttpClientInterface $client;

    public function __construct(#[\SensitiveParameter] string $stripeToken)
    {
        $this->client = HttpClient::create([
            'auth_basic' => [$stripeToken, ''],
        ]);
    }

    /**
     * @param array<string, mixed> $routeParams
     *
     * @return string The URL to redirect the user to for payment
     */
    public function pay(int $amount, string $url): string
    {
        $response = $this->client->request('POST', self::STRIPE_API_URL, [
            'body' => [
                'mode' => 'payment',
                'success_url' => $url,
                'cancel_url' => $url,

                'line_items[0][price_data][currency]' => 'eur',
                'line_items[0][price_data][unit_amount]' => $amount,
                'line_items[0][price_data][product_data][name]' => 'Game Purchase',
                'line_items[0][quantity]' => 1,
            ],
        ]);

        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException('Stripe payment failed');
        }

        $content = $response->toArray();

        return $content['url'] ?? throw new \RuntimeException('Stripe payment URL not found');
    }
}

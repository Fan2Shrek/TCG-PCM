<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Tests\Functional\FunctionalTestCase;

final class BoosterApiTest extends FunctionalTestCase
{
    private const URI = '/api/boosters/open';

    public function testSuccessfulBoosterOpen(): void
    {
        $this->post(self::URI);

        $response = $this->client->getResponse();
        $this->assertSame(200, $response->getStatusCode());

        $data = json_decode($response->getContent() ?? '', true);
        $this->assertArrayHasKey('cards', $data);
        $this->assertCount(1, $data['cards']); // Assuming a booster contains 5 cards
    }
}

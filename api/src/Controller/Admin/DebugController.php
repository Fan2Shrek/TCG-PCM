<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Service\Game\CardFactoryInterface;
use App\Service\Game\CardRegistryInterface;
use App\Service\Game\CardRuntimeMap;
use App\Service\Game\Pipeline\GamePipeline;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/debug')]
final class DebugController extends AbstractController
{
    public function __construct(
        private CardRegistryInterface $cardRegistry,
        private CardFactoryInterface $factory,
    ) {
    }

    #[Route]
    public function view(): Response
    {
        return $this->render('pages/debug_view.html.twig', [
            'cards' => array_map($this->factory->create(...), $this->cardRegistry->getAllBy([])),
        ]);
    }

    #[Route('/add', methods: ['POST'])]
    public function add(
        Request $request,
        GamePipeline $gamePipeline,
    ): Response
    {
        $data = $request->toArray();

        $gamePipeline->start();

        return $this->json([
            'ok'
        ]);
    }
}

<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Entity\Card;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface; 

#[Route('/api/card', name: 'api_card_')]
#[OA\Tag(name: 'Card', description: 'Routes for all about cards')]
class ApiCardController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger
    ) {
    }
    #[Route('/all', name: 'List all cards', methods: ['GET'])]
    #[OA\Put(description: 'Return all cards in the database')]
    #[OA\Response(response: 200, description: 'List all cards')]
    public function cardAll(): Response
    {
        $this->logger->info('API cardAll');
        $cards = $this->entityManager->getRepository(Card::class)->findAll();
        return $this->json($cards);
    }

    #[Route('/{uuid}', name: 'Show card', methods: ['GET'])]
    #[OA\Parameter(name: 'uuid', description: 'UUID of the card', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Put(description: 'Get a card by UUID')]
    #[OA\Response(response: 200, description: 'Show card')]
    #[OA\Response(response: 404, description: 'Card not found')]
    public function cardShow(string $uuid): Response
    {
        $this->logger->info('API cardShow');
        $card = $this->entityManager->getRepository(Card::class)->findOneBy(['uuid' => $uuid]);
        if (!$card) {
            return $this->json(['error' => 'Card not found'], 404);
        }
        return $this->json($card);
    }

    #[Route('/search', name: 'search_cards', methods: ['GET'])]
    #[OA\Get(
        summary: "Search cards by name and setCode",
        parameters: [
            new OA\Parameter(name: 'q', in: 'query', required: true, description: 'Card name (at least 3 characters)', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'setCode', in: 'query', required: false, description: 'Set code of the card', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Returns matching cards'),
        ]
    )]
    public function searchCards(Request $request): Response
    {
        $query = $request->query->get('q', '');
        $setCode = $request->query->get('setCode', null);

        if (strlen($query) < 3) {
            return $this->json([]);
        }

        $qb = $this->entityManager->getRepository(Card::class)
            ->createQueryBuilder('c')
            ->where('c.name LIKE :name')
            ->setParameter('name', '%' . $query . '%')
            ->setMaxResults(20);

        if ($setCode) {
            $qb->andWhere('c.setCode = :setCode')
            ->setParameter('setCode', $setCode);
        }

        $cards = $qb->getQuery()->getResult();

        $this->logger->info('API searchCards', ['query' => $query, 'setCode' => $setCode]);

        return $this->json($cards);
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    #[OA\Get(
        summary: "List all cards with pagination and optional setCode filter",
        parameters: [
            new OA\Parameter(name: 'setCode', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of cards'),
        ]
    )]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $setCode = $request->query->get('setCode');
        $page = max($request->query->getInt('page', 1), 1);

        $qb = $this->entityManager->getRepository(Card::class)->createQueryBuilder('c');

        if ($setCode) {
            $qb->where('c.setCode = :setCode')->setParameter('setCode', $setCode);
        }

        $pagination = $paginator->paginate(
            $qb,
            $page,
            100 // 100 карточек на странице
        );

        $this->logger->info('API index paginated', ['page' => $page, 'setCode' => $setCode]);

        return $this->json([
            'cards' => $pagination->getItems(),
            'page' => $page,
            'totalPages' => ceil($pagination->getTotalItemCount() / 100),
            'totalItems' => $pagination->getTotalItemCount()
        ]);
    }
}

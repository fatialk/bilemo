<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Client;
use Doctrine\ORM\EntityManager;
use App\Repository\UserRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/clients')]
class ClientsController extends AbstractController
{
    #[Route('/', name: 'app_clients', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN', message: 'You don\'t have rights to access this page')]
    public function getClientsList(ClientRepository $clientRepository, SerializerInterface $serializer): JsonResponse
    {
        $clientsList = $clientRepository->findAll();
        $jsonClientsList = $serializer->serialize($clientsList, 'json', ['groups' => 'getClients']);
        return new JsonResponse($jsonClientsList, Response::HTTP_OK, [], true);
    }

    #[Route('/users', name: 'detailClient', methods: ['GET'])]
    public function getDetailClient(#[CurrentUser] ?Client $connectedClient, SerializerInterface $serializer,
    Request $request, UserRepository $userRepository, TagAwareCacheInterface $cachePool): JsonResponse {

        $clientId = $connectedClient->getId();
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 3);
        $idCache = "getAllRelatedUsers-" . $page . "-" . $limit;
        $relatedUsers = $cachePool->get($idCache, function (ItemInterface $item) use ($clientId, $userRepository, $page, $limit) {
            $item->tag("relatedUsersCache");
            return $userRepository->findUsersByClientIdWithPagination($clientId, $page, $limit);
        });
        
        $jsonClient = $serializer->serialize($relatedUsers, 'json', ['groups' => 'getClients']);
        return new JsonResponse($jsonClient, Response::HTTP_OK, ['accept' => 'json'], true);

    }
}


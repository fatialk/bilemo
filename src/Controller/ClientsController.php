<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
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

    #[Route('/{reference}', name: 'detailClient', methods: ['GET'])]
    public function getDetailClient(Client $client, SerializerInterface $serializer, ClientRepository $clientRepository): JsonResponse {

        $jsonClient = $serializer->serialize($client, 'json', ['groups' => 'getClients']);
        return new JsonResponse($jsonClient, Response::HTTP_OK, ['accept' => 'json'], true);
   }
}


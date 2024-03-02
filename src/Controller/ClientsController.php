<?php

namespace App\Controller;

use App\Repository\ClientRepository;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/clients')]
class ClientsController extends AbstractController
{
     /**
     * Cette méthode permet de récupérer l'ensemble des clients.
     *
     * @param ClientRepository $clientRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/', name: 'app_clients', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN', message: 'You don\'t have rights to access this page')]
    public function getClientsList(ClientRepository $clientRepository, SerializerInterface $serializer): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(['groups' => 'getUsers']);
        $clientsList = $clientRepository->findAll();
        $jsonClientsList = $serializer->serialize($clientsList, 'json', $context);
        return new JsonResponse($jsonClientsList, Response::HTTP_OK, [], true);
    }


}


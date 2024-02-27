<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Client;
use App\Repository\UserRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/users')]
class UsersController extends AbstractController
{
    #[Route('/', name: 'app_users', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN', message: 'You don\'t have rights to access this page')]
    public function getUsersList(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $usersList = $userRepository->findAll();
        $jsonUsersList = $serializer->serialize($usersList, 'json', ['groups' => 'getUsers']);
        return new JsonResponse($jsonUsersList, Response::HTTP_OK, [], true);
    }

    #[Route('/', name:"createUser", methods: ['POST'])]
    public function createUser(#[CurrentUser] ?Client $connectedClient, Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ClientRepository $clientRepository, ValidatorInterface $validator): JsonResponse
    {

        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        // On vérifie les erreurs
        $errors = $validator->validate($user);

        if ($errors->count() > 0) {
        return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        // Récupération du client_id.
        $clientId = $connectedClient->getId();

        // On cherche le client qui correspond et on l'assigne à l'utilisateur.
        // Si "find" ne trouve pas le client, alors null sera retourné.
        $user->setClient($clientRepository->find($clientId));

        $em->persist($user);
        $em->flush();

        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUsers']);

        $location = $urlGenerator->generate('detailUser', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["Location" => $location], true);
   }

   #[Route('/{id}', name: 'detailUser', methods: ['GET'])]
    public function getDetailUser(#[CurrentUser] ?Client $connectedClient, User $user, SerializerInterface $serializer): JsonResponse {

        if($connectedClient == $user->getClient()){
        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getUsers']);
        return new JsonResponse($jsonUser, Response::HTTP_OK, ['accept' => 'json'], true);
        }else{
            return new JsonResponse('You don\'t have rights to access this page');
        }
   }

   #[Route('/{id}', name: 'deleteUser', methods: ['DELETE'])]
    public function deleteUser(#[CurrentUser] ?Client $connectedClient, User $user, EntityManagerInterface $em, TagAwareCacheInterface $cachePool): JsonResponse
    {
        if($connectedClient == $user->getClient()){
        // On vide le cache.
        $cachePool->invalidateTags(["relatedUsersCache"]);
        
        $em->remove($user);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }else{
            return new JsonResponse('You don\'t have rights to delete this user');
        }
    }

   #[Route('/{id}', name:"updateUser", methods:['PUT'])]
   public function updateUser(#[CurrentUser] ?Client $connectedClient, Request $request, SerializerInterface $serializer, User $currentUser, EntityManagerInterface $em, ClientRepository $clientRepository, ValidatorInterface $validator, UrlGeneratorInterface $urlGenerator, TagAwareCacheInterface $cachePool): JsonResponse
   {
    if($connectedClient == $currentUser->getClient()){
        $updatedUser = $serializer->deserialize($request->getContent(),
               User::class,
               'json',
               [AbstractNormalizer::OBJECT_TO_POPULATE => $currentUser]);

        // On vérifie les erreurs
       $errors = $validator->validate($updatedUser);

       if ($errors->count() > 0) {
       return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
       }

       $clientId = $connectedClient->getId();
       $updatedUser->setClient($clientRepository->find($clientId));

       $em->persist($updatedUser);
       $em->flush();

       $jsonUser = $serializer->serialize($updatedUser, 'json', ['groups' => 'getUsers']);

       $location = $urlGenerator->generate('detailUser', ['id' => $updatedUser->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        // On vide le cache.
        $cachePool->invalidateTags(["relatedUsersCache"]);

       return new JsonResponse($jsonUser, Response::HTTP_OK, ["Location" => $location], true);
    }else{
        return new JsonResponse('You don\'t have rights to update this user');
    }
  }
}


<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Client;
use App\Repository\UserRepository;
use App\Repository\ClientRepository;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

#[Route('/users')]
class UsersController extends AbstractController
{
     /**
     * Cette méthode permet de récupérer la liste des utilisateurs relatifs au client connecté.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste des utilisateurs relatifs à au client connecté",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=User::class, groups={"getUsers"}))
     *     )
     * )
     *
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="La page que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     *
     * @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Le nombre d'éléments que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     * @OA\Tag(name="Users")
     *
     * @param ?Client $connectedClient
     * @param SerializerInterface $serializer
     * @param Request $request
     * @param UserRepository $userRepository
     * @param TagAwareCacheInterface $cachePool
     * @return JsonResponse
     */
    #[Route('/', name: 'user_list', methods: ['GET'])]
    public function getList(#[CurrentUser] ?Client $connectedClient, SerializerInterface $serializer,
    Request $request, UserRepository $userRepository, TagAwareCacheInterface $cachePool): JsonResponse {

        $context = SerializationContext::create()->setGroups(['groups' => 'getUsers']);
        $clientId = $connectedClient->getId();
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 3);
        $tag1 = "users-" . $clientId . "-" . $page . "-" . $limit;
        $relatedUsers = $cachePool->get($tag1, function (ItemInterface $item) use ($tag1, $clientId, $userRepository, $page, $limit) {
            $globalTag = "users-".$clientId;
            $item->tag($tag1, $globalTag);
            return $userRepository->findUsersByClientIdWithPagination($clientId, $page, $limit);
        });
        $jsonClient = $serializer->serialize($relatedUsers, 'json', $context);
        return new JsonResponse($jsonClient, Response::HTTP_OK, ['accept' => 'json'], true);

    }

     /**
     * Cette méthode permet au client connecté de créer un nouvel utilisateur.
     *
     * @OA\RequestBody(
     *  required=true,
     *  @OA\JsonContent(
     *  example={
     *    "email": "username@gmail.com",
     *    "first_name": "lina",
     *    "last_name" : "ahmed"
     *   }
     *   )
     *  )
     * @OA\Response(
     *     response=201,
     *     description="Crée un utilisateur lié au client connecté",
     * )
     * @OA\Tag(name="Users")
     *
     * @param ?Client $connectedClient
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @param ClientRepository $clientRepository
     * @param ValidatorInterface $validator
     * @param TagAwareCacheInterface $cachePool
     * @return JsonResponse
     */
    #[Route('/', name:"user_create", methods: ['POST'])]
    public function createUser(#[CurrentUser] ?Client $connectedClient, Request $request,
    SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator,
    ClientRepository $clientRepository, ValidatorInterface $validator, TagAwareCacheInterface $cachePool): JsonResponse
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

         // On vide le cache.
         $globalTag = "users-".$connectedClient->getId();
         $cachePool->invalidateTags([$globalTag]);

        $context = SerializationContext::create()->setGroups(['groups' => 'getUsers']);
        $jsonUser = $serializer->serialize($user, 'json', $context);

        $location = $urlGenerator->generate('user_detail', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["Location" => $location], true);
    }


     /**
     * Cette méthode permet au client connecté de récupérer un utilisateur en particulier en fonction de son id.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne un utilisateur en particulier",
     * )
     *
     * @OA\Tag(name="Users")
     *
     * @param ?Client $connectedClient
     * @param User $user
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'user_detail', methods: ['GET'])]
    public function getDetailUser(#[CurrentUser] ?Client $connectedClient, User $user,
    SerializerInterface $serializer): JsonResponse {

        $context = SerializationContext::create()->setGroups(['getUsers']);
        if($connectedClient == $user->getClient()){
            $jsonUser = $serializer->serialize($user, 'json', $context);
            return new JsonResponse($jsonUser, Response::HTTP_OK, ['accept' => 'json'], true);
        }else{
            return new JsonResponse('You don\'t have rights to access this page');
        }
    }

     /**
     * Cette méthode permet au client connecté de supprimer un utilisateur en fonction de son id.
     *
     * @OA\Tag(name="Users")
     *
     * @param ?Client $connectedClient
     * @param User $user
     * @param EntityManagerInterface $em
     * @param TagAwareCacheInterface $cachePool
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'user_delete', methods: ['DELETE'])]
    public function deleteUser(#[CurrentUser] ?Client $connectedClient, User $user,
    EntityManagerInterface $em, TagAwareCacheInterface $cachePool): JsonResponse
    {
        if($connectedClient == $user->getClient()){
            // On vide le cache.
            $globalTag = "users-".$connectedClient->getId();
            $cachePool->invalidateTags([$globalTag]);

            $em->remove($user);
            $em->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }else{
            return new JsonResponse('You don\'t have rights to delete this user');
        }
    }

      /**
     * Cette méthode permet au client connecté de mettre à jour un utilisateur en fonction de son id.
     *
     * @OA\RequestBody(
     *  required=true,
     *  @OA\JsonContent(
     *  example={
     *    "email": "username@gmail.com",
     *    "first_name": "lina",
     *    "last_name" : "ahmed"
     *   }
     *   )
     *  )
     * @OA\Response(
     *     response=204,
     *     description="met à jour l'utilisateur dont l'id doit être renseigné en paramètre",
     *  )
     *
     * @OA\Tag(name="Users")
     *
     * @param ?Client $connectedClient
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param User $currentUser
     * @param EntityManagerInterface $em
     * @param ClientRepository $clientRepository
     * @param ValidatorInterface $validator
     * @param TagAwareCacheInterface $cachePool
     * @return JsonResponse
     */
    #[Route('/{id}', name:"user_update", methods:['PUT'])]
    public function updateUser(#[CurrentUser] ?Client $connectedClient, Request $request,
    SerializerInterface $serializer, User $currentUser, EntityManagerInterface $em,
    ClientRepository $clientRepository, ValidatorInterface $validator, TagAwareCacheInterface $cachePool): JsonResponse
    {
        if($connectedClient == $currentUser->getClient()){
            $newUser = $serializer->deserialize($request->getContent(),
            User::class,
            'json');

            $currentUser->setFirstName($newUser->getFirstName());
            $currentUser->setLastName($newUser->getLastName());
            $currentUser->setEmail($newUser->getEmail());


            // On vérifie les erreurs
            $errors = $validator->validate($currentUser);

            if ($errors->count() > 0) {
                return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
            }

            $clientId = $connectedClient->getId();
            $currentUser->setClient($clientRepository->find($clientId));

            $em->persist($currentUser);
            $em->flush();

             // On vide le cache.
             $globalTag = "users-".$connectedClient->getId();
             $cachePool->invalidateTags([$globalTag]);

            //    return new JsonResponse($jsonUser, Response::HTTP_OK, ["Location" => $location], true);
            return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
        }else{
            return new JsonResponse('You don\'t have rights to update this user');
        }
    }
}


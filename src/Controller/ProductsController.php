<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductsController extends AbstractController
{
    #[Route('/products', name: 'app_products', methods: ['GET'])]
    public function getProductsList(ProductRepository $productRepository, SerializerInterface $serializer): JsonResponse
    {
        $productsList = $productRepository->findAll();
        $jsonProductsList = $serializer->serialize($productsList, 'json');
        return new JsonResponse($jsonProductsList, Response::HTTP_OK, [], true);

        // return new JsonResponse([
        //     'message' => 'Welcome to your new controller!',
        //     'path' => 'src/Controller/ProductsController.php',
        // ]);
    }
}

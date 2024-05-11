<?php

namespace App\Controller;

use Predis\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class VisitController extends AbstractController
{
    private Client $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    #[Route('/update', name: 'update', methods: ['POST'])]
    public function update(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $content = json_decode($request->getContent(), true);
        $country = $content['country'] ?? null;

        // Validate the country parameter
        $errors = $validator->validate($country, [
            new Assert\NotBlank(),
            new Assert\Length(['min' => 2, 'max' => 2]),
            new Assert\Regex('/^[A-Za-z]{2}$/'),
        ]);

        // Check for validation errors
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['error' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $country = strtoupper($country);

        $this->redis->zincrby('countries', 1, $country);
        return new JsonResponse("Statistics updated for country $country");
    }

    #[Route('/statistics', name: 'statistics', methods: ['GET'])]
    public function statistics(): JsonResponse
    {
        $countriesWithCounts = $this->redis->zrange('countries', 0, -1, 'WITHSCORES');
        return new JsonResponse($countriesWithCounts);
    }
}

<?php

namespace App\Controller;

use Redis;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class VisitController extends AbstractController
{
    private Redis $redis;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    #[Route('/update', name: 'update', methods: ['POST'])]
    public function update(Request $request, ValidatorInterface $validator): Response
    {
        $content = json_decode($request->getContent(), true, 2, JSON_THROW_ON_ERROR);
        $country = $content['country'] ?? null;

        // Validate the country parameter
        $errors = $validator->validate($country, [
            new Assert\NotBlank(message: 'country is required'),
            new Assert\Regex('/^[A-Za-z]{2}$/', message: 'country must be a two-letter code'),
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
        return new Response();
    }

    #[Route('/statistics', name: 'statistics', methods: ['GET'])]
    public function statistics(): JsonResponse
    {
        $countriesWithCounts = $this->redis->zrange('countries', 0, -1, true);

        return new JsonResponse($countriesWithCounts);
    }
}

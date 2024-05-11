<?php

namespace App\Tests\Controller;

use App\Controller\VisitController;
use PHPUnit\Framework\TestCase;
use Predis\Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class VisitControllerTest extends TestCase
{
    private VisitController $visitController;

    protected function setUp(): void
    {
        $redisClient = $this->createMock(Client::class);
        $this->visitController = new VisitController($redisClient);
    }

    public function testUpdateValidCountry(): void
    {
        $request = new Request(content: json_encode(['country' => 'US']));

        $validator = $this->createMock(ValidatorInterface::class);
        $validator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList([]));

        $response = $this->visitController->update($request, $validator);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testUpdateInvalidCountry(): void
    {
        $request = new Request(content: json_encode(['country' => 'USA']));

        $validator = $this->createMock(ValidatorInterface::class);
        $validator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList([
                new ConstraintViolation('Country must consist of two letters.', null, [], '', 'country', ''),
            ]));

        $response = $this->visitController->update($request, $validator);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testStatistics(): void
    {
        $redisClient = $this->getMockBuilder(Client::class)
            ->addMethods(['zrange'])
            ->getMock();

        $redisClient->expects($this->once())
            ->method('zrange')
            ->willReturn(['US' => 10, 'CA' => 5]);

        $visitController = new VisitController($redisClient);

        $response = $visitController->statistics();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $expectedData = ['US' => 10, 'CA' => 5];
        $this->assertEquals($expectedData, json_decode($response->getContent(), true));
    }
}


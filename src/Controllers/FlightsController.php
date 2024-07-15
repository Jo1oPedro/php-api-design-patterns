<?php

namespace App\Controllers;

use App\Entity\Flight;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

readonly class FlightsController extends ApiController
{
    public function index(Request $request, Response $response): Response
    {
        //Retrieve flights data
        $flights = $this->entityManager
            ->getRepository(Flight::class)
            ->findAll();

        if(!$flights) {
            return $response->withStatus(StatusCodeInterface::STATUS_NO_CONTENT);
        }

        //Serialize the flights
        $jsonFlights = $this->serializer->serialize(
            ["Flights" => $flights],
            $request->getAttribute("content-type")->format()
        );

        //Return the response containing the flights
        $response->getBody()->write($jsonFlights);
        return $response->withHeader("Cache-Control", "public, max-age=600");
    }

    public function show(Request $request, Response $response, string $number): Response
    {
        $flight = $this->entityManager->getRepository(Flight::class)
            ->findOneBy(["number" =>$number]);

        if(!$flight) {
            return $response->withStatus(StatusCodeInterface::STATUS_NOT_FOUND);
        }

        $jsonFlight = $this->serializer->serialize(
            ["flight" => $flight],
            $request->getAttribute("content-type")->format()
        );

        $response->getBody()->write($jsonFlight);

        return $response->withHeader("Cache-Control", "public, max-age=600");
    }

    public function store(Request $request, Response $response): Response
    {
        //Grab the post data
        $flightJson = $request->getBody()->getContents();

        //deserialize into a flight
        $flight = $this->serializer->deserialize(
            $flightJson,
            Flight::class,
            $request->getAttribute("content-type")->format()
        );

        //Validate the post data

        //Save the flight to the DB
        $this->entityManager->persist($flight);
        $this->entityManager->flush();

        //Serialize the new Flight
        $jsonFlight = $this->serializer->serialize(
          ["flight" => $flight],
          $request->getAttribute("content-type")->format()
        );

        //Add the flight on the response body
        $response->getBody()->write($jsonFlight);

        //Return the new response
        return $response->withStatus(StatusCodeInterface::STATUS_CREATED);
    }

    public function update(Request $request, Response $response, string $number): Response
    {
        $flight = $this->entityManager->getRepository(Flight::class)
            ->findOneBy(["number" => $number]);

        if(!$flight) {
            return $response->withStatus(StatusCodeInterface::STATUS_NOT_FOUND);
        }

        $flightJson = $request->getBody()->getContents();

        $flight = $this->serializer->deserialize(
            $flightJson,
            Flight::class,
            $request->getAttribute("content-type")->format(),
            [AbstractNormalizer::OBJECT_TO_POPULATE => $flight]
        );

        $this->entityManager->persist($flight);
        $this->entityManager->flush();

        $jsonFlight = $this->serializer->serialize(
            ["flight" => $flight],
            $request->getAttribute("content-type")->format()
        );

        $response->getBody()->write($jsonFlight);

        return $response;
    }

    public function destroy(Request $request, Response $response, string $number): Response
    {
        $flight = $this->entityManager->getRepository(Flight::class)
            ->findOneBy(["number" => $number]);

        if(!$flight) {
            return $response->withStatus(StatusCodeInterface::STATUS_NOT_FOUND);
        }

        $this->entityManager->remove($flight);
        $this->entityManager->flush();

        return $response->withStatus(StatusCodeInterface::STATUS_NO_CONTENT);
    }
}
<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

/**
 * Class LoggerController
 * @package App\Controller
 *
 * @Route(path="/api")
 */
class LoggerController
{

    public function __construct(LoggerInterface $padbolLogger)
    {
        $this->logger = $padbolLogger;
    }

    /**
     * @Route("/logger", name="logger", methods={"POST"})
     */
    public function logger(Request $request): JsonResponse
    {
        $parameters = json_decode($request->getContent(), true);

        $this->logger->info("Logger", $parameters);

        return new JsonResponse(
            [], 
            Response::HTTP_OK
        );
    }

}

?>

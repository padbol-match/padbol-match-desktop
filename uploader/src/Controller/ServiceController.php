<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ServiceRepository;

/**
 * Class ServiceController
 * @package App\Controller
 *
 * @Route(path="/api")
 */
class ServiceController
{

    public function __construct(ServiceRepository $serviceRepository)
    {
        $this->serviceRepository = $serviceRepository;
    }

    /**
     * @Route("/get-fields/{userId}", name="get-fields", methods={"GET"})
     */
    public function getFields(string $userId): JsonResponse
    {
        $service = $this->serviceRepository->findByUserId($userId);
        $fields = [];

        for($i=1; $i<=$service[0]->getMaxCapacity();$i++){
            $fields[] = [
                "id" => $i,
                "press" => 0 
            ];
        }

        return new JsonResponse(
            $fields, 
            Response::HTTP_OK
        );
    }

}

?>

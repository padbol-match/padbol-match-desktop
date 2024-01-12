<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\VimeoService;

/**
 * Class VimeoController
 * @package App\Controller
 *
 * @Route(path="/api/vimeo")
 */
class VimeoController
{

    public function __construct(VimeoService $vimeoService)
    {
        $this->vimeoService = $vimeoService;
    }

    /**
     * @Route("/get-credentials", name="get-vimeo-credentials", methods={"POST"})
     */
    public function getCredentials(Request $request): JsonResponse
    {
        return new JsonResponse(
            $this->vimeoService->getCredentials(), 
            Response::HTTP_OK
        );
    }

}

?>

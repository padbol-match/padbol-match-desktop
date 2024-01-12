<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\WordPressService;

/**
 * Class SecurityController
 * @package App\Controller
 *
 * @Route(path="/api")
 */
class SecurityController
{

    public function __construct(WordPressService $wordPressService)
    {
        $this->wordPressService = $wordPressService;
    }

    /**
     * @Route("/login", name="login", methods={"POST"})
     */
    public function login(Request $request): JsonResponse
    {

        $username = $request->get('username');
        $password = $request->get('password');

        return new JsonResponse(
            $this->wordPressService->login($username, $password), 
            Response::HTTP_OK
        );
    }

}

?>

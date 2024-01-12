<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\WordPressService;
use App\Services\EmailService;
use App\Repository\AppointmentRepository;

/**
 * Class PostController
 * @package App\Controller
 *
 * @Route(path="/api")
 */
class PostController
{

    public function __construct(
        WordPressService $wordPressService, 
        EmailService $emailService,
        AppointmentRepository $appointmentRepository
        )
    {
        $this->wordPressService = $wordPressService;
        $this->emailService = $emailService;
        $this->appointmentRepository = $appointmentRepository;
    }

    /**
     * @Route("/post", name="post", methods={"POST"})
     */
    public function post(Request $request): JsonResponse
    {
        $parameters = json_decode($request->getContent(), true);
        
        $email = $parameters['username'];
        $url = $parameters['url'];
        $token = $parameters['token'];
        $title = $parameters['title'];
        $appointmentNumber = $parameters['appointment_number'];

        $appointment = $this->appointmentRepository->findOneByid($appointmentNumber);

        $publish = $this->wordPressService->post($email, $url, $token, $title);

        if(isset($publish["error"])){
            return new JsonResponse(
                $publish, 
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        $this->emailService->sendEmailToUser(
            $appointment->getCustomer()->getEmail(),
            $appointment->getCustomer()->getFirstName(),
            $publish["link"]);

        return new JsonResponse(
            $publish, 
            Response::HTTP_OK
        );
    }

}

?>

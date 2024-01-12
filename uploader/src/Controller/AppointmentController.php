<?php
namespace App\Controller;

use App\Repository\AppointmentRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use \DateTime;
use \DateTimeZone;

/**
 * Class AppointmentController
 * @package App\Controller
 *
 * @Route(path="/api")
 */
class AppointmentController
{
    private $appointmentRepository;

    public function __construct(AppointmentRepository $appointmentRepository)
    {
        $this->appointmentRepository = $appointmentRepository;
    }

    /**
     * @Route("/appointments/today/{userId}/{gmtMinutes}", name="appointments_today", methods={"GET"})
     */
    public function getAllByUserCurrentDay(string $userId, string $gmtMinutes): JsonResponse
    {
        $appointments = $this->appointmentRepository->findByUserForToday($userId, $gmtMinutes);
        $data = [];
        $fieldNumber = 0;
        $lastStartTime = 0;

        foreach ($appointments as $appointment) {
            if($lastStartTime != $appointment->getStartTime()){
                $fieldNumber = 1;
            }else{
                $fieldNumber++;
            }
            $data[] = $this->transformAppointment($appointment, $fieldNumber, $gmtMinutes);
            $lastStartTime = $appointment->getStartTime();
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * @Route("/appointments/week/{userId}/{gmtMinutes}", name="appointments_week", methods={"GET"})
     */
    public function getAllByUserCurrentWeek(string $userId, string $gmtMinutes): JsonResponse
    {
        $appointments = $this->appointmentRepository->findByUserForCurrentWeek($userId);
        $data = [];
        $fieldNumber = 0;
        $lastStartTime = 0;

        foreach ($appointments as $appointment) {
            if($lastStartTime != $appointment->getStartTime()){
                $fieldNumber = 1;
            }else{
                $fieldNumber++;
            }
            $data[] = $this->transformAppointment($appointment, $fieldNumber, $gmtMinutes);
            $lastStartTime = $appointment->getStartTime();
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    private function transformAppointment($appointment, $fieldNumber, $gmtMinutes){
        $startTime = $appointment->getStartTime();
        $endTime = $appointment->getEndTime();
        
        $timezone = new DateTimeZone(timezone_name_from_abbr("",(-1) * intval($gmtMinutes) * 60 , 0));
        $startDateTime = new DateTime(date('Y-m-d H:i:s', $startTime));
        $startDateTime->setTimezone($timezone);
        $endDateTime = new DateTime(date('Y-m-d H:i:s', $endTime));
        $endDateTime->setTimezone($timezone);
        //$endDateTime->add(new DateInterval('PT' . $appointment->getDuration() . 'M'));

        return [
            'id' => $appointment->getId(),
            'appointment_number' => $appointment->getId(),
            'customer_name' => $appointment->getCustomer()->getFirstName() . " " . $appointment->getCustomer()->getLastName(),
            'customer_email' => $appointment->getCustomer()->getEmail(),
            'start_date' => $startDateTime,
            'end_date' => $endDateTime,
            'field' => strval($fieldNumber),
            'duration' => $appointment->getDuration()
        ];
    }
}

?>

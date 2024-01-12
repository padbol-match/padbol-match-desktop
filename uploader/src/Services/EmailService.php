<?php
namespace App\Services;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Psr\Log\LoggerInterface;

class EmailService
{
    private $mailer;

    public function __construct(
        MailerInterface $mailer,
        LoggerInterface $padbolLogger)
    {
        $this->mailer = $mailer;
        $this->logger = $padbolLogger;
    }

    public function sendEmailToUser($userEmail, $userName, $urlPost)
    {
        $email = (new TemplatedEmail())
            ->from('no_reply@padbol.com')
            ->to($userEmail)
            //->cc('cc@example.com')
            ->bcc('todotresde@gmail.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Padbol Match! Mejores Jugadas')
            ->htmlTemplate('emails/post.html.twig')
            ->textTemplate('emails/post.txt.twig')
            ->context([
                'user' => $userName,
                'url_post' => $urlPost,
            ]);

        $this->mailer->send($email);

        $this->logger->info("Success Send Email", [
            "userEmail" => $userEmail, 
            "userName" => $userName, 
            "urlPost" => $urlPost
        ]);
    }

}
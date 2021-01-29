<?php
namespace App\Controller;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class Mail 
{
    public function sendEmail(MailerInterface $mailer, $to, $subject, $text)
    {

        $email = (new Email())
            ->from('c291985550-a0047f@inbox.mailtrap.io')
            ->to($to)
            ->subject($subject)
            ->text($text)
            ->html('<p>See Twig integration for better HTML integration!</p>');

        $mailer->send($email);

    }
}
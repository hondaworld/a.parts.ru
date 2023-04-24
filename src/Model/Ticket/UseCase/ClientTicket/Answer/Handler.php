<?php

namespace App\Model\Ticket\UseCase\ClientTicket\Answer;

use App\Model\Expense\Entity\ShippingPlace\ShippingPlaceRepository;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Ticket\Entity\ClientTicket\ClientTicket;
use App\Model\Ticket\Entity\ClientTicketAnswer\ClientTicketAnswer;
use App\Model\Ticket\Entity\ClientTicketAnswer\ClientTicketAnswerRepository;
use App\Model\Ticket\Entity\ClientTicketAttach\ClientTicketAttach;
use App\Model\Ticket\Entity\ClientTicketAttach\ClientTicketAttachRepository;
use App\Service\Email\EmailSender;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Handler
{
    private Flusher $flusher;
    private ClientTicketAttachRepository $clientTicketAttachRepository;
    private ClientTicketAnswerRepository $clientTicketAnswerRepository;
    private EmailSender $emailSender;
    private ParameterBagInterface $parameterBag;

    public function __construct(
        ClientTicketAnswerRepository $clientTicketAnswerRepository,
        ClientTicketAttachRepository $clientTicketAttachRepository,
        EmailSender                  $emailSender,
        ParameterBagInterface        $parameterBag,
        Flusher                      $flusher
    )
    {
        $this->flusher = $flusher;
        $this->clientTicketAttachRepository = $clientTicketAttachRepository;
        $this->clientTicketAnswerRepository = $clientTicketAnswerRepository;
        $this->emailSender = $emailSender;
        $this->parameterBag = $parameterBag;
    }

    public function handle(Command $command, ClientTicket $ticket, Manager $manager): void
    {
//        $query = "SELECT user_email, userID FROM client_tickets WHERE ticketID = '".AddSlashes($ticketID)."'";
//        $res = mysql_query($query);
//        $user_email = mysql_result($res, 0, "user_email");
//        $userID = mysql_result($res, 0, "userID");
//        if ($userID != 0)
//        {
//            $query = "SELECT email_send FROM users WHERE userID = '".AddSlashes($userID)."' AND isEmail = 1";
//            $res1 = mysql_query($query);
//            if (mysql_num_rows($res1) > 0)
//            {
//                $user_email = htmlspecialchars(StripSlashes(mysql_result($res1, 0, "email_send")));
//            }
//            else
//            {
//                $query = "SELECT email FROM contacts a WHERE a.userID = '".AddSlashes($userID)."' AND isMain = 1";
//                $res1 = mysql_query($query);
//                if (mysql_num_rows($res1) > 0)
//                {
//                    $user_email = htmlspecialchars(StripSlashes(mysql_result($res1, 0, "email")));
//                }
//            }
//        }

        if ($ticket->getUser()) {
            $userEmail = $ticket->getUser()->getEmail()->getValueWithCheck();
        } else {
            $userEmail = $ticket->getUserEmail() == '' ? null : $ticket->getUserEmail();
        }

        $answer = new ClientTicketAnswer(
            $ticket,
            $command->text,
            $manager
        );
        $this->clientTicketAnswerRepository->add($answer);
        $ticket->answering($manager);

        if ($command->attach) {
            $attach = new ClientTicketAttach($command->attach);
            $answer->addAttach($attach);
        }

        $lastUserAnswer = $this->clientTicketAnswerRepository->findLastUserAnswer($ticket);

        $text = $command->text;

        if ($lastUserAnswer) {
            $text .= "<br><br>";
            $text .= $lastUserAnswer->getDateofadded()->format('d.m.Y') . ' в ' . $lastUserAnswer->getDateofadded()->format('H:i:s') . ' &lt;' . ($userEmail ?: '') . '&gt; написал(а):<br><br>';
            $text .= $lastUserAnswer->getText();
        }


        if ($userEmail) {
            $text .= "<br><br>";
            $text .= "При переписке, пожалуйста, не меняйте тему сообщения<br><br>";

            // TODO Поменять адрес сайта

            if ($ticket->getUser()) $text .= "Полную переписку можно прочитать, перейдя по <a href='http://passport.parts.ru/tickets/index.php?ticketID=" . $ticket->getId() . "'>ссылке</a>";

            $attaches = [];

            if ($command->attach) {
                $ticket_directory = $this->parameterBag->get('user_ticket_attach_directory');
                $attaches[] = [
                    'body' => file_get_contents($ticket_directory . '/' . $command->attach),
                    'fileName' => $command->attach
                ];
            }

            $this->emailSender->sendEmail($userEmail, "Re: TicketID: " . $ticket->getId(), $text, $attaches);
        }

        $this->flusher->flush();
    }
}

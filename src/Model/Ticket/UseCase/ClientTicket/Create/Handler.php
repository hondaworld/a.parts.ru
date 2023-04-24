<?php

namespace App\Model\Ticket\UseCase\ClientTicket\Create;

use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Ticket\Entity\ClientTicket\ClientTicket;
use App\Model\Ticket\Entity\ClientTicket\ClientTicketRepository;
use App\Model\Ticket\Entity\ClientTicketAnswer\ClientTicketAnswer;
use App\Model\Ticket\Entity\ClientTicketAnswer\ClientTicketAnswerRepository;
use App\Model\Ticket\Entity\ClientTicketAttach\ClientTicketAttach;
use App\Model\Ticket\Entity\ClientTicketGroup\ClientTicketGroupRepository;
use App\Model\User\Entity\User\User;
use App\ReadModel\Ticket\ClientTicketFetcher;
use App\Service\Email\EmailSender;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Handler
{
    private Flusher $flusher;
    private ClientTicketAnswerRepository $clientTicketAnswerRepository;
    private EmailSender $emailSender;
    private ParameterBagInterface $parameterBag;
    private ClientTicketRepository $clientTicketRepository;
    private ClientTicketFetcher $clientTicketFetcher;
    private ClientTicketGroupRepository $clientTicketGroupRepository;

    public function __construct(
        ClientTicketRepository       $clientTicketRepository,
        ClientTicketFetcher          $clientTicketFetcher,
        ClientTicketGroupRepository  $clientTicketGroupRepository,
        ClientTicketAnswerRepository $clientTicketAnswerRepository,
        EmailSender                  $emailSender,
        ParameterBagInterface        $parameterBag,
        Flusher                      $flusher
    )
    {
        $this->flusher = $flusher;
        $this->clientTicketAnswerRepository = $clientTicketAnswerRepository;
        $this->emailSender = $emailSender;
        $this->parameterBag = $parameterBag;
        $this->clientTicketRepository = $clientTicketRepository;
        $this->clientTicketFetcher = $clientTicketFetcher;
        $this->clientTicketGroupRepository = $clientTicketGroupRepository;
    }

    public function handle(Command $command, User $user, Manager $manager): void
    {
        $userEmail = $user->getEmail()->getValueWithCheck();

        $ticket_num = $this->clientTicketFetcher->getNextDocumentNumber();

        $ticket = new ClientTicket(
            $this->clientTicketGroupRepository->get($command->groupID),
            $user,
            $ticket_num,
            $command->user_subject
        );
        $this->clientTicketRepository->add($ticket);

        $answer = new ClientTicketAnswer(
            $ticket,
            $command->text,
            $manager
        );
        $this->clientTicketAnswerRepository->add($answer);
        $ticket->answering($manager);
        $ticket->open($manager);
        $ticket->read();

        if ($command->attach) {
            $attach = new ClientTicketAttach($command->attach);
            $answer->addAttach($attach);
        }

        $text = $command->text;

        $this->flusher->flush();

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

    }
}

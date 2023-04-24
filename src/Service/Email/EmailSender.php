<?php

namespace App\Service\Email;

use App\Model\User\Entity\User\User;
use DomainException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class EmailSender
{
    private MailerInterface $mailer;
    private Environment $twig;
    private ParameterBagInterface $parameterBag;

    public function __construct(
        MailerInterface       $mailer,
        Environment           $twig,
        ParameterBagInterface $parameterBag
    )
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->parameterBag = $parameterBag;
    }

    public function send(User $user, string $subject, string $text, array $attaches = []): void
    {
        if (!$userEmail = $user->getEmail()->getValueWithCheck()) {
            throw new DomainException('Клиент не имеет e-mail или не подтвердил его');
        }

        $this->sendEmail($userEmail, $subject, $text, $attaches);
    }

    public function sendWithFullCheck(User $user, string $subject, string $text, array $attaches = []): void
    {
        if (empty($user->getEmail()->getValue())) {
            throw new DomainException('У пользователя нет e-mail');
        }
        if (!$user->getEmail()->isActivated()) {
            throw new DomainException('E-mail пользователя не активирован');
        }
        if (!$user->getEmail()->isNotification()) {
            throw new DomainException('Пользователь запретил отсылать e-mail');
        }

        $this->send($user, $subject, $text, $attaches);
    }

    public function sendEmail(string $userEmail, string $subject, string $text, array $attaches = [], string $from = 'parts@parts.ru'): void
    {
        $images_directory = $this->parameterBag->get('images_directory');

        try {
            $html = $this->twig->render('mail/mail.html.twig', ['text' => $text]);
        } catch (LoaderError | SyntaxError | RuntimeError $e) {
            throw new DomainException($e->getMessage());
        }

        $arTo = explode(', ', $userEmail);

        $email = (new Email())
            ->from($from)
            ->subject($subject)
            ->html($html)
            ->embedFromPath($images_directory . '/email/logo.png', 'logo')
            ->embedFromPath($images_directory . '/email/viber.png', 'viber')
            ->embedFromPath($images_directory . '/email/vk.png', 'vk')
            ->embedFromPath($images_directory . '/email/whatsapp.png', 'whatsapp');

        foreach ($arTo as $to) {
            $email->addTo($to);
        }

        foreach ($attaches as $attach) {
            $email->attach($attach['body'], $attach['fileName'], $attach['fileType'] ?? null);
        }

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            throw new DomainException($e->getMessage());
        }
    }
}
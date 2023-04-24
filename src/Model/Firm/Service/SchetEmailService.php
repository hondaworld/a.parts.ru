<?php

namespace App\Model\Firm\Service;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Finance\Entity\NalogNds\NalogNdsRepository;
use App\Model\Firm\Entity\Schet\Schet;
use App\Model\User\Entity\Template\Template;
use App\Model\User\Entity\Template\TemplateRepository;
use App\Service\Email\EmailSender;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class SchetEmailService
{
    private TemplateRepository $templateRepository;
    private EmailSender $emailSender;
    private ParameterBagInterface $parameterBag;
    private Environment $twig;
    private NalogNdsRepository $nalogNdsRepository;
    private ZapCardRepository $zapCardRepository;
    private MailerInterface $mailer;

    public function __construct(
        MailerInterface       $mailer,
        TemplateRepository    $templateRepository,
        EmailSender           $emailSender,
        ParameterBagInterface $parameterBag,
        Environment           $twig,
        NalogNdsRepository    $nalogNdsRepository,
        ZapCardRepository     $zapCardRepository
    )
    {
        $this->templateRepository = $templateRepository;
        $this->emailSender = $emailSender;
        $this->parameterBag = $parameterBag;
        $this->twig = $twig;
        $this->nalogNdsRepository = $nalogNdsRepository;
        $this->zapCardRepository = $zapCardRepository;
        $this->mailer = $mailer;
    }

    public function emailWithUrl(Schet $schet): string
    {
        $user = $schet->getUser();

        $template = $this->templateRepository->get(
            $schet->isPayCreditCard() ?
                Template::EMAIL_CREDIT_CARD_LINK :
                Template::EMAIL_SCHET_LINK
        );

        if ($schet->isPayCreditCard()) {
            $text = $template->getText([
                'url' => "<a href='http://passport.parts.ru/pay.php?schetID=" . $schet->getId() . "'>Оплата №" . $schet->getDocument()->getSchetNum() . "</a>"
            ]);
        } else {
            $text = $template->getText([
                'url' => "<a href='http://passport.parts.ru/schet.php?schetID=" . $schet->getId() . "'>Счет №" . $schet->getDocument()->getSchetNum() . "</a>"
            ]);
        }

        $this->emailSender->sendWithFullCheck($user, $template->getSubject(), $text);

        return $user->getEmail()->getValue();

    }

    public function emailWithPdf(Schet $schet): string
    {
        $schetData = ['schet' => $schet] + $this->getSchetData($schet);
        $images_directory = $this->parameterBag->get('images_directory');

        $CreaterPdf = new SchetCreatePdf($schet);
        $CreaterPdf->setContent($this->twig->render('app/firms/schet/print/content.html.twig', $schetData));
        $CreaterPdf->setSignature($images_directory . '/schet_signature.png');
        $CreaterPdf->setBottom($this->twig->render('app/firms/schet/print/footer.html.twig', $schetData));
        $attach = $CreaterPdf->save();

        $user = $schet->getUser();

        $template = $this->templateRepository->get(
            $schet->isPayCreditCard() ?
                Template::EMAIL_CREDIT_CARD_LINK :
                Template::EMAIL_SCHET_LINK
        );

        if ($schet->isPayCreditCard()) {
            $text = $template->getText([
                'url' => "<a href='http://passport.parts.ru/pay.php?schetID=" . $schet->getId() . "'>Оплата №" . $schet->getDocument()->getSchetNum() . "</a>"
            ]);
        } else {
            $text = $template->getText([
                'url' => "<a href='http://passport.parts.ru/schet.php?schetID=" . $schet->getId() . "'>Счет №" . $schet->getDocument()->getSchetNum() . "</a>"
            ]);
        }

        $attaches = [];
        $attaches[] = [
            'body' => $attach,
            'fileName' => 'schet_' . $schet->getDocument()->getSchetNum() . '.pdf'
        ];
        $this->emailSender->sendWithFullCheck($user, $template->getSubject(), $text, $attaches);
        return $user->getEmail()->getValue();
    }

    public function getSchetData(Schet $schet): array
    {
        $arr = [];
        $arr['document_num'] = $schet->getDocument()->getDocumentNum();;
        $arr['document_date'] = $schet->getDateofadded();

        $nalogNds = $this->nalogNdsRepository->getLastByFirm($schet->getFirm(), $schet->getDateofadded());

        $arr['sum'] = 0;
        $arr['sumNds'] = 0;
        $arr['zapCards'] = [];
        $schetGoods = $schet->getSchetGoods();
        foreach ($schetGoods as $item) {
            $arr['zapCards'][$item->getId()] = $this->zapCardRepository->getByNumberAndCreaterID($item->getNumber()->getValue(), $item->getCreater()->getId());

            $price = $item->getPrice() * $item->getQuantity();
            $arr['sum'] += $price;
            $arr['sumNds'] += $price / (100 + $nalogNds->getNds()) * $nalogNds->getNds();
        }

        $arr['nds'] = $nalogNds->getNds();

        return $arr;
    }
}
<?php


namespace App\ReadModel\Ticket\Filter\ClientTicket;


use App\Form\Type\DateIntervalPickerType;
use App\Form\Type\InPageType;
use App\ReadModel\Firm\FirmFetcher;
use App\ReadModel\Manager\ManagerFetcher;
use App\ReadModel\Ticket\ClientTicketGroupFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private ManagerFetcher $managerFetcher;
    private ClientTicketGroupFetcher $clientTicketGroupFetcher;

    public function __construct(ManagerFetcher $managerFetcher, ClientTicketGroupFetcher $clientTicketGroupFetcher)
    {
        $this->managerFetcher = $managerFetcher;
        $this->clientTicketGroupFetcher = $clientTicketGroupFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('inPage', InPageType::class)
            ->add('ticket_num', Type\TextType::class, ['filter' => true])
            ->add('text', Type\TextType::class, ['filter' => true])
            ->add('answered', Type\TextType::class, ['filter' => true])
            ->add('managerClosed', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip($this->managerFetcher->assoc()),
                'placeholder' => 'Закрыл',
                'attr' => ['onchange' => 'this.form.submit()']
            ])
            ->add('groupID', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip($this->clientTicketGroupFetcher->assoc()),
                'placeholder' => 'Департамент',
                'attr' => ['onchange' => 'this.form.submit()']
            ])
            ->add('dateofanswer', DateIntervalPickerType::class, []);;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Filter::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}
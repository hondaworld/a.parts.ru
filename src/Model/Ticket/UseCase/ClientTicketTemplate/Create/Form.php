<?php

namespace App\Model\Ticket\UseCase\ClientTicketTemplate\Create;

use App\ReadModel\Ticket\ClientTicketGroupFetcher;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private ClientTicketGroupFetcher $clientTicketGroupFetcher;

    public function __construct(ClientTicketGroupFetcher $clientTicketGroupFetcher)
    {
        $this->clientTicketGroupFetcher = $clientTicketGroupFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, ['label' => 'Наименование'])
            ->add('text', CKEditorType::class, ['required' => false, 'label' => 'Шаблон'])
            ->add('client_ticket_groups', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Менеджеры',
                'choices' => array_flip($this->clientTicketGroupFetcher->assoc()),
                'expanded' => false,
                'multiple' => true,
                'attr' => ['size' => 10]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

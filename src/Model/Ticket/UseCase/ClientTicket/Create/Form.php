<?php

namespace App\Model\Ticket\UseCase\ClientTicket\Create;


use App\Form\Type\FileUploadType;
use App\ReadModel\Ticket\ClientTicketGroupFetcher;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\AbstractType;
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
            ->add('text', CKEditorType::class, ['label' => false])
            ->add('user_subject', Type\TextType::class, ['label' => 'Тема'])
            ->add('groupID', Type\ChoiceType::class, [
                'label' => 'Департамент',
                'choices' => array_flip($this->clientTicketGroupFetcher->assocForManager($options['data']->manager)),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
            ])
            ->add('attach', FileUploadType::class, [
                'label' => 'Прикрепленный файл',
                'is_vertical' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

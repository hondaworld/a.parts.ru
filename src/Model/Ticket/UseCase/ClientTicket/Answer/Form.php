<?php

namespace App\Model\Ticket\UseCase\ClientTicket\Answer;


use App\Form\Type\FileUploadType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('text', CKEditorType::class, ['label' => false, 'config_name' => 'small'])
            ->add('attach', FileUploadType::class, [
                'label' => 'Прикрепленный файл',
                'is_vertical' => true
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

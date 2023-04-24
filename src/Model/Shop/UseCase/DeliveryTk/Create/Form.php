<?php

namespace App\Model\Shop\UseCase\DeliveryTk\Create;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, ['label' => 'Наименование'])
            ->add('http', Type\TextType::class, ['required' => false, 'label' => 'Адрес'])
            ->add('sms_text', Type\TextareaType::class, ['required' => false, 'label' => 'SMS сообщение (макс. 255 символов)', 'attr' => ['maxLength' => 255]])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

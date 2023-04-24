<?php

namespace App\Model\User\UseCase\Discount\Create;


use App\Form\Type\FloatNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('summ', FloatNumberType::class, ['label' => 'Пороговая сумма'])
            ->add('discount_spare', FloatNumberType::class, ['label' => 'Скидка на запчасти'])
            ->add('discount_service', FloatNumberType::class, ['label' => 'Скидка на услуги'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

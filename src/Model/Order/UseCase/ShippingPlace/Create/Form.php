<?php

namespace App\Model\Order\UseCase\ShippingPlace\Create;


use App\Form\Type\FloatNumberType;
use App\Form\Type\IntegerNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number', IntegerNumberType::class, ['label' => 'Номер места'])
            ->add('length', IntegerNumberType::class, ['label' => 'Длина, см'])
            ->add('width', IntegerNumberType::class, ['label' => 'Ширина, см'])
            ->add('height', IntegerNumberType::class, ['label' => 'Высота, см'])
            ->add('weight', FloatNumberType::class, ['label' => 'Вес, кг'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

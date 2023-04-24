<?php

namespace App\Model\Firm\UseCase\Schet\Pay;


use App\Form\Type\DatePickerType;
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
            ->add('summ', FloatNumberType::class, ['label' => 'Сумма'])
            ->add('dateofpaid', DatePickerType::class, ['label' => 'Дата добавления'])
            ->add('isEmail', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'Отправить счет клиенту по e-mail', 'label_attr' => ['class' => 'switch-custom']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

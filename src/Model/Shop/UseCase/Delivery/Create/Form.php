<?php

namespace App\Model\Shop\UseCase\Delivery\Create;


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
            ->add('name', Type\TextType::class, ['label' => 'Наименование'])
            ->add('porog', FloatNumberType::class, ['required' => false, 'label' => 'Порог'])
            ->add('x1', FloatNumberType::class, ['required' => false, 'label' => 'Значение меньше порога'])
            ->add('isPercent1', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Единица измерения порога',
                'choices' => [
                    'руб.' => false,
                    '%' => true,
                ],
                'placeholder' => false
            ])
            ->add('x2', FloatNumberType::class, ['required' => false, 'label' => 'Значение больше порога'])
            ->add('isPercent2', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Единица измерения порога',
                'choices' => [
                    'руб.' => false,
                    '%' => true,
                ],
                'placeholder' => false
            ])
            ->add('isTK', Type\CheckboxType::class, ['required' => false, 'label' => 'Является ТК', 'label_attr' => ['class' => 'switch-custom']])
            ->add('isOwnDelivery', Type\CheckboxType::class, ['required' => false, 'label' => 'Клиент может оплачивать сам', 'label_attr' => ['class' => 'switch-custom']])
            ->add('isMain', Type\CheckboxType::class, ['required' => false, 'label' => 'Основная доставка', 'label_attr' => ['class' => 'switch-custom']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

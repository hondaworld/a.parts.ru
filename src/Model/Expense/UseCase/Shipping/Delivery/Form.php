<?php

namespace App\Model\Expense\UseCase\Shipping\Delivery;


use App\Form\Type\DatePickerType;
use App\ReadModel\Shop\DeliveryTkFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private DeliveryTkFetcher $deliveryTkFetcher;

    public function __construct(DeliveryTkFetcher $deliveryTkFetcher)
    {
        $this->deliveryTkFetcher = $deliveryTkFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('pay_type', Type\ChoiceType::class, [
            'required' => false,
            'label' => 'Оплачивает',
            'choices' => [
                'Отправитель' => 1,
                'Получатель' => 2
            ],
            'placeholder' => '',
        ])
            ->add('delivery_tkID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'ТК',
                'choices' => array_flip($this->deliveryTkFetcher->assoc()),
                'placeholder' => ''
            ])
            ->add('tracknumber', Type\TextType::class, ['required' => false, 'label' => 'Трекинг номер', 'attr' => ['maxLength' => 50]])
            ->add('dateofadded', DatePickerType::class, ['label' => 'Дата']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

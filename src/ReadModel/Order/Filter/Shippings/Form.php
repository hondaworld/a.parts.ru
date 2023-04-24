<?php


namespace App\ReadModel\Order\Filter\Shippings;


use App\Form\Type\DateIntervalPickerType;
use App\Form\Type\InPageType;
use App\ReadModel\Firm\FirmFetcher;
use App\ReadModel\Order\ShippingStatusFetcher;
use App\ReadModel\Shop\DeliveryTkFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private FirmFetcher $firmFetcher;
    private ShippingStatusFetcher $shippingStatusFetcher;
    private DeliveryTkFetcher $deliveryTkFetcher;

    public function __construct(FirmFetcher $firmFetcher, ShippingStatusFetcher $shippingStatusFetcher, DeliveryTkFetcher $deliveryTkFetcher)
    {
        $this->firmFetcher = $firmFetcher;
        $this->shippingStatusFetcher = $shippingStatusFetcher;
        $this->deliveryTkFetcher = $deliveryTkFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('inPage', InPageType::class)
            ->add('user_name', Type\TextType::class, ['filter' => true,
                'attr' => ['placeholder' => false, 'style' => 'width: 120px;']
            ])
            ->add('gruz_firm_name', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip($this->firmFetcher->assoc()),
                'placeholder' => '',
                'attr' => ['onchange' => 'this.form.submit()', 'style' => 'width: 120px;']
            ])
            ->add('gruz_user_town', Type\TextType::class, [
                'filter' => true,
                'attr' => ['placeholder' => false, 'style' => 'width: 120px;']
            ])
            ->add('pay_type_name', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => [
                    'Отправитель' => 1,
                    'Получатель' => 2
                ],
                'placeholder' => '',
                'attr' => ['onchange' => 'this.form.submit()', 'style' => 'width: 120px;']
            ])
            ->add('delivery_tkID', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip($this->deliveryTkFetcher->assoc()),
                'placeholder' => '',
                'attr' => ['onchange' => 'this.form.submit()', 'style' => 'width: 120px;']
            ])
            ->add('tracknumber', Type\TextType::class, [
                'filter' => true,
                'attr' => ['placeholder' => false, 'style' => 'width: 120px;']
            ])
            ->add('status', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip($this->shippingStatusFetcher->assocNormal()),
                'placeholder' => '',
                'attr' => ['onchange' => 'this.form.submit()', 'style' => 'width: 120px;']
            ])
            ->add('dateofadded', DateIntervalPickerType::class, []);
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
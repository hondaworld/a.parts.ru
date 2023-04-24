<?php

namespace App\Model\Provider\UseCase\Price\Edit;

use App\Form\Type\FloatNumberType;
use App\Form\Type\IntegerNumberType;
use App\ReadModel\Finance\CurrencyFetcher;
use App\ReadModel\Provider\ProviderFetcher;
use App\ReadModel\Provider\ProviderPriceGroupFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private $currencyFetcher;
    private $providerPriceGroupFetcher;
    private $providerFetcher;

    public function __construct(CurrencyFetcher $currencyFetcher, ProviderFetcher $providerFetcher, ProviderPriceGroupFetcher $providerPriceGroupFetcher)
    {

        $this->currencyFetcher = $currencyFetcher;
        $this->providerPriceGroupFetcher = $providerPriceGroupFetcher;
        $this->providerFetcher = $providerFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('providerPriceGroupID', Type\ChoiceType::class, [
                'label' => 'Группа',
                'choices' => array_flip($this->providerPriceGroupFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
            ])
            ->add('providerID', Type\ChoiceType::class, [
                'label' => 'Поставщик',
                'choices' => array_flip($this->providerFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
            ])
            ->add('name', Type\TextType::class, ['label' => 'Наименование', 'attr' => ['maxLength' => 50]])
            ->add('description', Type\TextType::class, ['label' => 'Описание'])
            ->add('srok', Type\TextType::class, ['label' => 'Срок', 'attr' => ['maxLength' => 25]])
            ->add('srokInDays', IntegerNumberType::class, ['label' => 'Срок в днях'])
            ->add('currencyID', Type\ChoiceType::class, [
                'label' => 'Валюта',
                'choices' => array_flip($this->currencyFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
            ])
            ->add('koef', FloatNumberType::class, ['label' => 'Коэффициент'])
            ->add('currencyOwn', FloatNumberType::class, ['required' => false, 'label' => 'Собственный курс'])
            ->add('deliveryForWeight', FloatNumberType::class, ['required' => false, 'label' => 'Доставка за кг'])
            ->add('deliveryInPercent', IntegerNumberType::class, ['required' => false, 'label' => 'Доставка в процентах'])
            ->add('discount', FloatNumberType::class, ['label' => 'Максимальная скидка деталей со складов'])
            ->add('daysofchanged', IntegerNumberType::class, ['label' => 'Количество дней просрочки прайс-листа'])
            ->add('clients_hide', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'Скрыть на сайтах', 'label_attr' => ['class' => 'switch-custom']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

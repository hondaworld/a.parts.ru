<?php


namespace App\ReadModel\Income\Filter\Income;


use App\Form\Type\DatePickerType;
use App\Form\Type\InPageType;
use App\ReadModel\Card\ZapCardAbcFetcher;
use App\ReadModel\Detail\CreaterFetcher;
use App\ReadModel\Income\IncomeStatusFetcher;
use App\ReadModel\Manager\ManagerFetcher;
use App\ReadModel\Provider\ProviderPriceFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private CreaterFetcher $createrFetcher;
    private IncomeStatusFetcher $incomeStatusFetcher;
    private ZapCardAbcFetcher $zapCardAbcFetcher;
    private ProviderPriceFetcher $providerPriceFetcher;
    private ManagerFetcher $managerFetcher;

    public function __construct(CreaterFetcher $createrFetcher, IncomeStatusFetcher $incomeStatusFetcher, ZapCardAbcFetcher $zapCardAbcFetcher, ProviderPriceFetcher $providerPriceFetcher, ManagerFetcher $managerFetcher)
    {
        $this->createrFetcher = $createrFetcher;
        $this->incomeStatusFetcher = $incomeStatusFetcher;
        $this->zapCardAbcFetcher = $zapCardAbcFetcher;
        $this->providerPriceFetcher = $providerPriceFetcher;
        $this->managerFetcher = $managerFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('inPage', InPageType::class)
            ->add('incomeID', Type\TextType::class, ['filter' => true, 'attr' => ['style' => 'max-width: 110px;'], 'label' => '#'])
            ->add('managerID', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip([0 => 'Нет'] + $this->managerFetcher->assocNicks(true)),
                'attr' => [
                    'class' => 'custom-select custom-select-sm form-control-alt'
                ],
                'placeholder' => '',
                'label' => 'Менеджер'
            ])
            ->add('dateofadded', DatePickerType::class, ['filter' => true, 'label' => 'Дата добавления'])
            ->add('dateofzakaz', DatePickerType::class, ['filter' => true, 'label' => 'Дата заказа'])
            ->add('dateofin', DatePickerType::class, ['filter' => true, 'label' => 'Дата прихода'])
            ->add('dateofinplan', DatePickerType::class, ['filter' => true, 'label' => 'План. дата прихода'])
            ->add('number', Type\TextType::class, ['filter' => true, 'attr' => ['style' => 'max-width: 110px;'], 'label' => 'Номер'])
            ->add('gtd', Type\TextType::class, ['filter' => true, 'attr' => ['style' => 'max-width: 110px;'], 'label' => 'ГТД'])
            ->add('abc', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip(['blank' => 'Пусто'] + $this->zapCardAbcFetcher->assoc()),
                'attr' => [
                ],
                'placeholder' => '',
                'label' => 'ABC'
            ])
            ->add('createrID', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip($this->createrFetcher->assoc()),
                'attr' => [
                    'style' => 'max-width: 100px;',
                ],
                'placeholder' => '',
                'label' => 'Бренд'
            ])
            ->add('status', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip($this->incomeStatusFetcher->assoc()),
                'attr' => [
                    'style' => 'max-width: 100px;',
                ],
                'placeholder' => '',
                'label' => 'Статус'
            ])
            ->add('orderID', Type\TextType::class, ['filter' => true, 'attr' => ['style' => 'max-width: 110px;'], 'label' => 'Заказ'])
//            ->add('isDoc', Type\ChoiceType::class, ['filter' => true, 'choices' => [
//                'Нет' => false,
//                'Да' => true
//            ], 'placeholder' => '',
//                'label' => 'Проверено'
//            ])
            ->add('isUnpack', Type\ChoiceType::class, ['filter' => true, 'choices' => [
                'Нет' => false,
                'Да' => true
            ], 'placeholder' => '',
                'label' => 'Посчитано'
            ])
            ->add('providerPriceID', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => $this->providerPriceFetcher->assocWithProvider(),
                'attr' => [
                    'size' => 20
                ],
                'multiple' => true,
                'expanded' => false,
                'placeholder' => '',
                'label' => 'Поставщик'
            ])
            ->add('incomeOrder', Type\TextType::class, ['filter' => true, 'attr' => ['style' => 'max-width: 70px;'], 'label' => 'Заказ поставщиков'])
            ->add('incomeDocument', Type\TextType::class, ['filter' => true, 'attr' => ['style' => 'max-width: 70px;'], 'label' => 'ПН'])
            ->add('isShowQuantityMskNull', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => '0 МСК ЦС', 'label_attr' => ['class' => 'checkbox-custom']])
            ->add('isShowQuantitySpbNull', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => '0 СПБ ЦС', 'label_attr' => ['class' => 'checkbox-custom']])
            ->add('isShowQuantitySrvNull', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => '0 СРВ ЮГ', 'label_attr' => ['class' => 'checkbox-custom']])
            ->add('isShowLessMskMax', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'МСК ЦС < Max', 'label_attr' => ['class' => 'checkbox-custom']])
            ->add('isShowLessSpbMax', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'СПБ ЦС < Max', 'label_attr' => ['class' => 'checkbox-custom']])
        ;
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
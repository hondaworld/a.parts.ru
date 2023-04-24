<?php

namespace App\ReadModel\Analytics\UseCase\ComparePrice;

use App\Form\Type\CsvUploadType;
use App\Form\Type\FloatNumberType;
use App\ReadModel\Provider\ProviderPriceFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private ProviderPriceFetcher $providerPriceFetcher;

    public function __construct(ProviderPriceFetcher $providerPriceFetcher)
    {
        $this->providerPriceFetcher = $providerPriceFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('isExcel', Type\HiddenType::class)
            ->add('file', CsvUploadType::class, [
                'label' => 'Файл с заменами',
                'attr' => [
                    'placeholder' => 'Выберите файл',
                ],
                'help' => 'Файл типа CSV. Первая колонка производитель, вторая - номер, третья - цена'
            ])
            ->add('providerPriceID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Поставщик',
                'choices' => $this->providerPriceFetcher->assocWithProvider(),
                'expanded' => false,
                'multiple' => true,
                'placeholder' => '',
                'attr' => [
                    'size' => 15
                ]
            ])
            ->add('days', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Отсекать измененных',
                'choices' => [
                    'Показать всех' => 0,
                    '1 неделя' => 7,
                    '1 месяц' => 30,
                    '3 месяца' => 90,
                    '6 месяцев' => 180,
                ],
                'expanded' => false,
                'multiple' => false,
                'placeholder' => false
            ])
            ->add('profit', FloatNumberType::class, ['label' => 'Наценка']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

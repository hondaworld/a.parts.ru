<?php

namespace App\Model\Order\UseCase\Good\CreateFile;

use App\Form\Type\CsvUploadType;
use App\Form\Type\IntegerNumberType;
use App\ReadModel\Detail\CreaterFetcher;
use App\ReadModel\Provider\ProviderPriceFetcher;
use App\ReadModel\Sklad\ZapSkladFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private CreaterFetcher $createrFetcher;
    private ZapSkladFetcher $zapSkladFetcher;
    private ProviderPriceFetcher $providerPriceFetcher;

    public function __construct(CreaterFetcher $createrFetcher, ZapSkladFetcher $zapSkladFetcher, ProviderPriceFetcher $providerPriceFetcher)
    {
        $this->createrFetcher = $createrFetcher;
        $this->zapSkladFetcher = $zapSkladFetcher;
        $this->providerPriceFetcher = $providerPriceFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('first_line', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'Не учитывать первую строчку', 'label_attr' => ['class' => 'switch-custom']])
            ->add('number_num', IntegerNumberType::class, [ 'label' => 'Поле с номером', 'attr' => ['maxLength' => 2]])
            ->add('creater_num', IntegerNumberType::class, ['required' => false, 'label' => 'Поле с производителем', 'attr' => ['maxLength' => 2]])
            ->add('quantity_num', IntegerNumberType::class, ['label' => 'Поле с количеством', 'attr' => ['maxLength' => 2]])
            ->add('price_num', IntegerNumberType::class, ['required' => false, 'label' => 'Поле с ценой', 'attr' => ['maxLength' => 2]])
            ->add('createrID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Производитель',
                'choices' => array_flip($this->createrFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => ''
            ])
            ->add('zapSkladID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Приоритетный склад отгрузки',
                'choices' => array_flip($this->zapSkladFetcher->assocForAddGoods()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => false
            ])
            ->add('providerPriceID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Поставщик: (Если будет выбран, склад не учтется)',
                'choices' => $this->providerPriceFetcher->assocWithProvider(),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => ''
            ])
            ->add('file', CsvUploadType::class, [
                'label' => 'Файл',
                'attr' => [
                    'placeholder' => 'Выберите файл',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

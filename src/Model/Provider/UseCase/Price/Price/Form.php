<?php

namespace App\Model\Provider\UseCase\Price\Price;

use App\ReadModel\Detail\CreaterFetcher;
use App\ReadModel\Finance\CurrencyFetcher;
use App\ReadModel\Provider\ProviderFetcher;
use App\ReadModel\Provider\ProviderPriceFetcher;
use App\ReadModel\Provider\ProviderPriceGroupFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private $createrFetcher;
    private $providerPriceFetcher;

    public function __construct(CreaterFetcher $createrFetcher, ProviderPriceFetcher $providerPriceFetcher)
    {

        $this->createrFetcher = $createrFetcher;
        $this->providerPriceFetcher = $providerPriceFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('superProviderPriceID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Прайс-лист родитель',
                'choices' => array_flip($this->providerPriceFetcher->assoc($options['data']->providerPriceID)),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
                'help' => 'При загрузке родителя все данные копируются сюда'
            ])
            ->add('createrID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Производитель',
                'choices' => array_flip($this->createrFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
            ])
            ->add('price', Type\TextType::class, ['required' => false, 'label' => 'Файл'])
            ->add('price_copy', Type\TextType::class, ['required' => false, 'label' => 'Файл копировать в файл'])
            ->add('price_email', Type\TextType::class, ['required' => false, 'label' => 'Часть наименования файла из e-mail'])
            ->add('email_from', Type\TextType::class, ['required' => false, 'label' => 'E-mail, от которого идет письмо с прайсом', 'help' => 'Предыдущий пункт обязательно должен быть заполнен'])
            ->add('isNotCheckExt', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'Не учитывать расширение (всегда csv)', 'label_attr' => ['class' => 'switch-custom']])
            ->add('priceadd', Type\TextType::class, ['required' => false, 'label' => 'Коэффициент, умножаемый на цену', 'attr' => ['class' => 'js-convert-float', 'maxLength' => 8]])
            ->add('razd', Type\TextType::class, ['required' => false, 'label' => 'Разделитель', 'attr' => ['maxLength' => 10]])
            ->add('razd_decimal', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Разделитель десятичных знаков',
                'choices' => array_flip($this->providerPriceFetcher->assocRazdDecimal()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => false,
            ])
            ->add('rg_value', Type\TextareaType::class, ['required' => false, 'label' => 'Значения RG (формат: RG;процент\n)'])
            ->add('isUpdate', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'Обновление', 'label_attr' => ['class' => 'switch-custom']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

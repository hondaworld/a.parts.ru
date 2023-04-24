<?php

namespace App\Model\Card\UseCase\Card\Price;


use App\Form\Type\FloatNumberType;
use App\ReadModel\Provider\ProviderPriceFetcher;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\AbstractType;
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
            ->add('price', FloatNumberType::class, ['required' => false, 'label' => 'Цена закупки, руб.'])
            ->add('currency_price', FloatNumberType::class, ['required' => false, 'label' => 'Цена закупки, у.е.'])
            ->add('currency_providerPriceID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Поставщик цены закупки',
                'choices' => $this->providerPriceFetcher->assocWithProvider($options['data']->currency_providerPriceID),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
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

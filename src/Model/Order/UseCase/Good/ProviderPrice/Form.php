<?php

namespace App\Model\Order\UseCase\Good\ProviderPrice;


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
            ->add('providerPriceID', Type\ChoiceType::class, [
                'required' => true,
                'label' => 'Поставщик',
                'choices' => $this->providerPriceFetcher->assocWithProvider(),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => ''
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}

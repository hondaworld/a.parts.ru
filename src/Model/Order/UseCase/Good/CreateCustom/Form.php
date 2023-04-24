<?php

namespace App\Model\Order\UseCase\Good\CreateCustom;


use App\ReadModel\Detail\CreaterFetcher;
use App\ReadModel\Provider\ProviderPriceFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private ProviderPriceFetcher $providerPriceFetcher;
    private CreaterFetcher $createrFetcher;

    public function __construct(ProviderPriceFetcher $providerPriceFetcher, CreaterFetcher $createrFetcher)
    {
        $this->providerPriceFetcher = $providerPriceFetcher;
        $this->createrFetcher = $createrFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('createrID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Бренд',
                'choices' => array_flip($this->createrFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => 'Бренд'
            ])
            ->add('providerPriceID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Поставщик',
                'choices' => $this->providerPriceFetcher->assocWithProvider(),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => 'Поставщик'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
            'csrf_protection' => false,
        ]);
    }
}

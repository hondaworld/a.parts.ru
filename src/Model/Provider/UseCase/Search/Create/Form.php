<?php

namespace App\Model\Provider\UseCase\Search\Create;

use App\Form\Type\FloatNumberType;
use App\Form\Type\IntegerNumberType;
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

    public function __construct(CreaterFetcher $createrFetcher, ProviderPriceFetcher $providerPriceFetcher)
    {
        $this->providerPriceFetcher = $providerPriceFetcher;
        $this->createrFetcher = $createrFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number', Type\TextType::class, ['label' => 'Номер', 'attr' => ['maxLength' => 30]])
            ->add('createrID', Type\ChoiceType::class, [
                'required' => true,
                'label' => 'Производитель',
                'choices' => array_flip($this->createrFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => ''
            ])
            ->add('price', FloatNumberType::class, ['label' => 'Цена'])
            ->add('quantity', IntegerNumberType::class, ['required' => false, 'label' => 'Количество'])
            ->add('providerPriceID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Поставщик цены закупки',
                'choices' => $this->providerPriceFetcher->assocWithProvider($options['data']->providerPriceID),
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

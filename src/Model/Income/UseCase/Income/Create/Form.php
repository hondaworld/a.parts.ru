<?php

namespace App\Model\Income\UseCase\Income\Create;


use App\Form\Type\IntegerNumberType;
use App\ReadModel\Detail\CreaterFetcher;
use App\ReadModel\Provider\ProviderPriceFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private CreaterFetcher $createrFetcher;
    private ProviderPriceFetcher $providerPriceFetcher;

    public function __construct(CreaterFetcher $createrFetcher, ProviderPriceFetcher $providerPriceFetcher)
    {
        $this->createrFetcher = $createrFetcher;
        $this->providerPriceFetcher = $providerPriceFetcher;
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
            ->add('quantity', IntegerNumberType::class, ['label' => 'Количество'])
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
        ]);
    }
}

<?php

namespace App\Model\Card\UseCase\Location\Create;


use App\Form\Type\IntegerNumberType;
use App\ReadModel\Shop\ShopLocationFetcher;
use App\ReadModel\Sklad\ZapSkladFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private ZapSkladFetcher $zapSkladFetcher;
    private ShopLocationFetcher $shopLocationFetcher;

    public function __construct(ZapSkladFetcher $zapSkladFetcher, ShopLocationFetcher $shopLocationFetcher)
    {
        $this->zapSkladFetcher = $zapSkladFetcher;
        $this->shopLocationFetcher = $shopLocationFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('zapSkladID', Type\ChoiceType::class, [
                'required' => true,
                'label' => 'Склад',
                'choices' => array_flip($this->zapSkladFetcher->assocZapCardEmpty($options['data']->zapCardID)),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => ''
            ])
            ->add('locationID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Ячейка',
                'choices' => array_flip($this->shopLocationFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => ''
            ])
            ->add('quantityMin', IntegerNumberType::class, ['label' => 'Минимальное количество'])
            ->add('quantityMax', IntegerNumberType::class, ['label' => 'Максимальное количество'])
            ->add('quantityMinIsReal', Type\CheckboxType::class, ['required' => false, 'label' => 'Минимальное количество закреплено', 'label_attr' => ['class' => 'switch-custom']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

<?php

namespace App\Model\Card\UseCase\Card\Dop;


use App\ReadModel\Card\EdIzmFetcher;
use App\ReadModel\Contact\CountryFetcher;
use App\ReadModel\Shop\ShopTypeFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private CountryFetcher $countryFetcher;
    private ShopTypeFetcher $shopTypeFetcher;
    private EdIzmFetcher $edIzmFetcher;

    public function __construct(CountryFetcher $countryFetcher, ShopTypeFetcher $shopTypeFetcher, EdIzmFetcher $edIzmFetcher)
    {
        $this->countryFetcher = $countryFetcher;
        $this->shopTypeFetcher = $shopTypeFetcher;
        $this->edIzmFetcher = $edIzmFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('countryID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Страна',
                'choices' => array_flip($this->countryFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => ''
            ])
            ->add('shop_typeID', Type\ChoiceType::class, [
                'required' => true,
                'label' => 'Тип',
                'choices' => array_flip($this->shopTypeFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => ''
            ])
            ->add('ed_izmID', Type\ChoiceType::class, [
                'required' => true,
                'label' => 'Единица измерения',
                'choices' => array_flip($this->edIzmFetcher->assoc()),
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

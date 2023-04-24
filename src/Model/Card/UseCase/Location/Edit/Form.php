<?php

namespace App\Model\Card\UseCase\Location\Edit;


use App\Form\Type\IntegerNumberType;
use App\ReadModel\Shop\ShopLocationFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private ShopLocationFetcher $shopLocationFetcher;

    public function __construct(ShopLocationFetcher $shopLocationFetcher)
    {
        $this->shopLocationFetcher = $shopLocationFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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

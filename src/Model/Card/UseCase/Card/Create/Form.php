<?php

namespace App\Model\Card\UseCase\Card\Create;


use App\ReadModel\Card\ZapGroupFetcher;
use App\ReadModel\Detail\CreaterFetcher;
use App\ReadModel\Shop\ShopTypeFetcher;
use App\ReadModel\Sklad\PriceGroupFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private CreaterFetcher $createrFetcher;
    private ShopTypeFetcher $shopTypeFetcher;
    private ZapGroupFetcher $zapGroupFetcher;
    private PriceGroupFetcher $priceGroupFetcher;

    public function __construct(CreaterFetcher $createrFetcher, ShopTypeFetcher $shopTypeFetcher, ZapGroupFetcher $zapGroupFetcher, PriceGroupFetcher $priceGroupFetcher)
    {
        $this->createrFetcher = $createrFetcher;
        $this->shopTypeFetcher = $shopTypeFetcher;
        $this->zapGroupFetcher = $zapGroupFetcher;
        $this->priceGroupFetcher = $priceGroupFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number', Type\TextType::class, ['label' => 'Номер', 'attr' => ['maxLength' => 30]])
            ->add('name', Type\TextType::class, ['required' => false, 'label' => 'Наименование'])
            ->add('description', Type\TextType::class, ['required' => false, 'label' => 'Описание'])
            ->add('createrID', Type\ChoiceType::class, [
                'required' => true,
                'label' => 'Производитель',
                'choices' => array_flip($this->createrFetcher->assoc()),
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
            ->add('zapGroupID', Type\ChoiceType::class, [
                'required' => true,
                'label' => 'Группа',
                'choices' => array_flip($this->zapGroupFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => ''
            ])
            ->add('price_groupID', Type\ChoiceType::class, [
                'required' => true,
                'label' => 'Группа прайс-листов',
                'choices' => array_flip($this->priceGroupFetcher->assoc()),
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

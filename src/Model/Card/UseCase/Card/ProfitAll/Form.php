<?php

namespace App\Model\Card\UseCase\Card\ProfitAll;

use App\Form\Type\FloatNumberNegativeType;
use App\Form\Type\FloatNumberType;
use App\Form\Type\IntegerNumberType;
use App\ReadModel\Sklad\PriceGroupFetcher;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private $priceGroupFetcher;

    public function __construct(PriceGroupFetcher $priceGroupFetcher)
    {
        $this->priceGroupFetcher = $priceGroupFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('price1', FloatNumberType::class, ['required' => false, 'label' => 'Цена реализации'])
            ->add('profit', IntegerNumberType::class, ['required' => false, 'label' => 'Наценка, %'])
            ->add('is_price_group_fix', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'Зафиксирован', 'label_attr' => ['class' => 'switch-custom']])
            ->add('price_groupID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Прайс-лист',
                'choices' => array_flip($this->priceGroupFetcher->assoc($options['data']->price_groupID)),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
            ]);
        foreach ($options['data']->opts as $opt) {
            $builder->add($options['data']->getProfit($opt->getId()), FloatNumberNegativeType::class, ['required' => false]);
            $builder->add($options['data']->getProfitPrice($opt->getId()), IntegerNumberType::class, ['required' => false]);
        }
        $builder->add($options['data']->getProfit(0), FloatNumberNegativeType::class, ['required' => false]);
        $builder->add($options['data']->getProfitPrice(0), IntegerNumberType::class, ['required' => false]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

<?php

namespace App\Model\Card\UseCase\Card\ProfitPriceGroup;

use App\ReadModel\Sklad\PriceGroupFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
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
            ->add('is_price_group_fix', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'Зафиксирован', 'label_attr' => ['class' => 'switch-custom']])
            ->add('price_groupID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Прайс-лист',
                'choices' => array_flip($this->priceGroupFetcher->assoc($options['data']->price_groupID)),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

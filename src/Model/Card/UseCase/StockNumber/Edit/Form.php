<?php

namespace App\Model\Card\UseCase\StockNumber\Edit;

use App\Form\Type\FloatNumberType;
use App\ReadModel\Card\ZapCardStockFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private ZapCardStockFetcher $zapCardStockFetcher;

    public function __construct(ZapCardStockFetcher $zapCardStockFetcher) {
        $this->zapCardStockFetcher = $zapCardStockFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('stockID', Type\ChoiceType::class, [
                'label' => 'Акция',
                'choices' => array_flip($this->zapCardStockFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
            ])
            ->add('price_stock', FloatNumberType::class, ['required' => false, 'label' => 'Цена'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

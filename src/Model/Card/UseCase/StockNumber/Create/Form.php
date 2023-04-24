<?php

namespace App\Model\Card\UseCase\StockNumber\Create;

use App\Form\Type\FloatNumberType;
use App\ReadModel\Detail\CreaterFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private CreaterFetcher $createrFetcher;

    public function __construct(CreaterFetcher $createrFetcher) {
        $this->createrFetcher = $createrFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numbers', Type\TextareaType::class, ['label' => 'Номера'])
            ->add('createrID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Производитель',
                'choices' => array_flip($this->createrFetcher->assoc()),
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

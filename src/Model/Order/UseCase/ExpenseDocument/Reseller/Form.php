<?php

namespace App\Model\Order\UseCase\ExpenseDocument\Reseller;

use App\ReadModel\Shop\ResellerFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private ResellerFetcher $resellerFetcher;

    public function __construct(ResellerFetcher $resellerFetcher)
    {
        $this->resellerFetcher = $resellerFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reseller_id', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Реселлер',
                'choices' => array_flip($this->resellerFetcher->assoc()),
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

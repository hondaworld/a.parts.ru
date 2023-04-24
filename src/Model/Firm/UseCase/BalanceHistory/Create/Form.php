<?php

namespace App\Model\Firm\UseCase\BalanceHistory\Create;

use App\Form\Type\FloatNumberNegativeType;
use App\ReadModel\Provider\ProviderFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private ProviderFetcher $providerFetcher;

    public function __construct(ProviderFetcher $providerFetcher)
    {
        $this->providerFetcher = $providerFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('providerID', Type\ChoiceType::class, [
                'required' => true,
                'label' => 'Поставщик',
                'choices' => array_flip($this->providerFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => ''
            ])
            ->add('balance', FloatNumberNegativeType::class, ['required' => true, 'label' => 'Сумма'])
            ->add('balance_nds', FloatNumberNegativeType::class, ['required' => false, 'label' => 'НДС'])
            ->add('description', Type\TextareaType::class, ['required' => false, 'label' => 'Комментарий'])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

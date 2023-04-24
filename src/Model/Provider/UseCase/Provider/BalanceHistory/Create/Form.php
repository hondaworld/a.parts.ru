<?php

namespace App\Model\Provider\UseCase\Provider\BalanceHistory\Create;

use App\Form\Type\FloatNumberNegativeType;
use App\ReadModel\Firm\FirmFetcher;
use App\ReadModel\Provider\ProviderFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private FirmFetcher $firmFetcher;

    public function __construct(FirmFetcher $firmFetcher)
    {
        $this->firmFetcher = $firmFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firmID', Type\ChoiceType::class, [
                'required' => true,
                'label' => 'Организация',
                'choices' => array_flip($this->firmFetcher->assocNotHide()),
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

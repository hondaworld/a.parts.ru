<?php

namespace App\Model\User\UseCase\BalanceHistory\EditFinanceType;

use App\ReadModel\Finance\FinanceTypeFetcher;
use App\ReadModel\Firm\FirmFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private FirmFetcher $firmFetcher;
    private FinanceTypeFetcher $financeTypeFetcher;

    public function __construct(FirmFetcher $firmFetcher, FinanceTypeFetcher $financeTypeFetcher)
    {
        $this->firmFetcher = $firmFetcher;
        $this->financeTypeFetcher = $financeTypeFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firmID', Type\ChoiceType::class, [
                'required' => true,
                'label' => 'Организация',
                'choices' => array_flip($this->firmFetcher->assocNotHide($options['data']->firmID)),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => ''
            ])
            ->add('finance_typeID', Type\ChoiceType::class, [
                'required' => true,
                'label' => 'Вид оплаты',
                'choices' => array_flip($this->financeTypeFetcher->assocWithFirm($options['data']->finance_typeID)),
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

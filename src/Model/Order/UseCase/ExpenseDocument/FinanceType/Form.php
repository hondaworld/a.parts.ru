<?php

namespace App\Model\Order\UseCase\ExpenseDocument\FinanceType;

use App\ReadModel\Expense\ExpenseTypeFetcher;
use App\ReadModel\Finance\FinanceTypeFetcher;
use App\ReadModel\Shop\ResellerFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private FinanceTypeFetcher $financeTypeFetcher;
    private ExpenseTypeFetcher $expenseTypeFetcher;
    private ResellerFetcher $resellerFetcher;

    public function __construct(FinanceTypeFetcher $financeTypeFetcher, ExpenseTypeFetcher $expenseTypeFetcher, ResellerFetcher $resellerFetcher)
    {

        $this->financeTypeFetcher = $financeTypeFetcher;
        $this->expenseTypeFetcher = $expenseTypeFetcher;
        $this->resellerFetcher = $resellerFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('finance_typeID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Вид платежа',
                'choices' => array_flip($this->financeTypeFetcher->assocWithFirm()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
            ])
            ->add('expense_type_id', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Тип отгрузки',
                'choices' => array_flip($this->expenseTypeFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
            ])
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

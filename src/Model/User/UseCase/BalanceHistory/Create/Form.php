<?php

namespace App\Model\User\UseCase\BalanceHistory\Create;

use App\Form\Type\FloatNumberNegativeType;
use App\Form\Type\FloatNumberType;
use App\ReadModel\Detail\CreaterFetcher;
use App\ReadModel\Finance\FinanceTypeFetcher;
use App\ReadModel\Firm\FirmFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private FinanceTypeFetcher $financeTypeFetcher;

    public function __construct(FinanceTypeFetcher $financeTypeFetcher)
    {
        $this->financeTypeFetcher = $financeTypeFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('finance_typeID', Type\ChoiceType::class, [
                'required' => true,
                'label' => 'Вид оплаты',
                'choices' => array_flip($this->financeTypeFetcher->assocWithFirm()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => ''
            ])
            ->add('balance', FloatNumberNegativeType::class, ['required' => true, 'label' => 'Сумма'])
            ->add('description', Type\TextareaType::class, ['required' => false, 'label' => 'Комментарий'])
            ->add('isSend', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'Отправить уведомление', 'label_attr' => ['class' => 'switch-custom']])
            ->add('schetID', Type\HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

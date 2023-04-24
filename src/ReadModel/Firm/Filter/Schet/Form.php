<?php


namespace App\ReadModel\Firm\Filter\Schet;


use App\Form\Type\DateIntervalPickerType;
use App\Form\Type\InPageType;
use App\Model\Firm\Entity\Schet\Schet;
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
            ->add('inPage', InPageType::class)
            ->add('finance_typeID', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip($this->financeTypeFetcher->assoc()),
                'placeholder' => '',
                'attr' => ['onchange' => 'this.form.submit()']
            ])
            ->add('firmID', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip($this->firmFetcher->assoc()),
                'placeholder' => '',
                'attr' => ['onchange' => 'this.form.submit()']
            ])
            ->add('status', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip(Schet::STATUSES),
                'placeholder' => '',
                'attr' => ['onchange' => 'this.form.submit()']
            ])
            ->add('isShowCanceled', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => [
                    'Скрыть отказные' => false,
                    'Все счета' => true
                ], 'placeholder' => false,
                'label' => false,
                'attr' => ['onchange' => 'this.form.submit()']
            ])
            ->add('dateofadded', DateIntervalPickerType::class, [])
            ->add('dateofpaid', DateIntervalPickerType::class, [])
            ->add('schet_num', Type\TextType::class, ['filter' => true, 'attr' => ['style' => 'max-width: 80px;']])
            ->add('user_name', Type\TextType::class, ['filter' => true])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Filter::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}
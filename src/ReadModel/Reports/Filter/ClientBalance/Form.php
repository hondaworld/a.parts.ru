<?php


namespace App\ReadModel\Reports\Filter\ClientBalance;


use App\ReadModel\Finance\FinanceTypeFetcher;
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
                'filter' => true,
                'choices' => array_flip($this->financeTypeFetcher->assoc()),
                'attr' => [
                    'onchange' => 'this.form.submit()'
                ],
                'placeholder' => ''
                ]);
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
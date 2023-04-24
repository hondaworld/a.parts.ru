<?php


namespace App\ReadModel\Expense\Filter\SchetFakKor;


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

    public function __construct(FirmFetcher $firmFetcher)
    {
        $this->firmFetcher = $firmFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('inPage', InPageType::class)
            ->add('firmID', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip($this->firmFetcher->assoc()),
                'placeholder' => '',
                'attr' => ['onchange' => 'this.form.submit()']
            ])
            ->add('dateofadded', DateIntervalPickerType::class, [])
            ->add('document_num', Type\TextType::class, ['filter' => true, 'attr' => ['style' => 'max-width: 80px;']])
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
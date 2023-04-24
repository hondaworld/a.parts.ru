<?php

namespace App\Model\Expense\UseCase\Shipping\Search;


use App\Form\Type\FloatNumberType;
use App\Form\Type\IntegerNumberType;
use App\Model\Finance\Entity\FinanceType\FinanceType;
use App\ReadModel\Document\DocumentTypeFetcher;
use App\ReadModel\Firm\FirmFetcher;
use App\ReadModel\Provider\ProviderFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('document_num', IntegerNumberType::class, ['required' => false, 'label' => 'Номер документа', 'attr' => ['size' => 15, 'maxLength' => 15, 'placeholder' => '#']])
            ->add('year', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Год',
                'choices' => range(date('Y'), 2013),
                'choice_value' => function ($value) {
                    return $value;
                },
                'choice_label' => function ($choice, $key, $value) use ($options) {
                    return $value . ' год';
                },
                'expanded' => false,
                'multiple' => false,
                'placeholder' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}

<?php

namespace App\Model\Firm\UseCase\AllDocuments\Search;


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
    private DocumentTypeFetcher $documentTypeFetcher;

    public function __construct(DocumentTypeFetcher $documentTypeFetcher)
    {
        $this->documentTypeFetcher = $documentTypeFetcher;
    }

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
            ->add('doc_typeID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Тип документа',
                'choices' => array_flip($this->documentTypeFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => 'Тип'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

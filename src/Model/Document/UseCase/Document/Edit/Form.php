<?php

namespace App\Model\Document\UseCase\Document\Edit;

use App\Form\Type\AutocompleteType;
use App\Form\Type\DatePickerType;
use App\Model\Beznal\UseCase\Beznal\Bank;
use App\ReadModel\Document\DocumentIdentificationFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    /**
     * @var DocumentIdentificationFetcher
     */
    private DocumentIdentificationFetcher $identificationFetcher;

    public function __construct(DocumentIdentificationFetcher $identificationFetcher)
    {

        $this->identificationFetcher = $identificationFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('doc_identID', Type\ChoiceType::class, ['label' => 'Идентификационный документ', 'choices' => array_flip($this->identificationFetcher->assoc()), 'placeholder' => ''])
            ->add('serial', Type\TextType::class, ['label' => 'Серия', 'attr' => ['maxLength' => 15]])
            ->add('number', Type\TextType::class, ['label' => 'Номер', 'attr' => ['maxLength' => 30]])
            ->add('organization', Type\TextareaType::class, ['label' => 'Кем выдан', 'required' => false])
            ->add('dateofdoc', DatePickerType::class, ['label' => 'Дата выдачи', 'required' => false])
            ->add('description', Type\TextareaType::class, ['label' => 'Дополнительная информация', 'required' => false])
            ->add('isMain', Type\CheckboxType::class, ['required' => false, 'label' => 'Основной документ', 'label_attr' => ['class' => 'switch-custom']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

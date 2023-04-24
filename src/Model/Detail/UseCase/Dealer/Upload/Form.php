<?php

namespace App\Model\Detail\UseCase\Dealer\Upload;

use App\Form\Type\CsvUploadType;
use App\Form\Type\FloatNumberType;
use App\Form\Type\IntegerNumberType;
use App\ReadModel\Detail\CreaterFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private CreaterFetcher $createrFetcher;

    public function __construct(CreaterFetcher $createrFetcher)
    {
        $this->createrFetcher = $createrFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', CsvUploadType::class, [
                'label' => 'Файл с дилерскими ценами',
                'attr' => [
                    'placeholder' => 'Выберите файл',
                ]
            ])
            ->add('createrID', Type\ChoiceType::class, [
                'label' => 'Производитель',
                'choices' => array_flip($this->createrFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
            ])
            ->add('numNumber', IntegerNumberType::class, ['label' => 'Поле с номером'])
            ->add('numPrice', IntegerNumberType::class, ['label' => 'Поле с ценой'])
            ->add('koef', FloatNumberType::class, ['required' => false, 'label' => 'Коэффициент, умножаемый на цену'])
            ->add('isDelete', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'Удалить цены указанного производителя'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

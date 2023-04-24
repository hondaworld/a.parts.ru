<?php

namespace App\Model\Provider\UseCase\Invoice\Create;

use App\Form\Type\FloatNumberType;
use App\Form\Type\IntegerNumberType;
use App\ReadModel\Income\IncomeStatusFetcher;
use App\ReadModel\Shop\DeleteReasonFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private IncomeStatusFetcher $incomeStatusFetcher;
    private DeleteReasonFetcher $deleteReasonFetcher;

    public function __construct(IncomeStatusFetcher $incomeStatusFetcher, DeleteReasonFetcher $deleteReasonFetcher)
    {
        $this->incomeStatusFetcher = $incomeStatusFetcher;
        $this->deleteReasonFetcher = $deleteReasonFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status_from', Type\ChoiceType::class, [
                'label' => 'Начальный статус',
                'choices' => array_flip($this->incomeStatusFetcher->assocExcludeDeleted()),
                'expanded' => false,
                'multiple' => true,
                'attr' => ['class' => 'js-select2', 'size' => 1]
            ])
            ->add('status_to', Type\ChoiceType::class, [
                'label' => 'Конечный статус',
                'choices' => array_flip($this->incomeStatusFetcher->assocExcludeDeleted()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
            ])
            ->add('status_none', Type\ChoiceType::class, [
                'label' => 'Статус отсутствия',
                'choices' => array_flip($this->incomeStatusFetcher->assocDeleted()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
            ])
            ->add('deleteReasonID', Type\ChoiceType::class, [
                'label' => 'Причина отсутствия',
                'choices' => array_flip($this->deleteReasonFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
            ])
            ->add('price', Type\TextType::class, ['required' => false, 'label' => 'Файл'])
            ->add('price_email', Type\TextType::class, ['required' => false, 'label' => 'Часть наименования файла из e-mail'])
            ->add('email_from', Type\TextType::class, ['required' => false, 'label' => 'E-mail, от которого идет письмо с прайсом', 'help' => 'Предыдущий пункт обязательно должен быть заполнен'])
            ->add('num_number_type', Type\ChoiceType::class, [
                'label' => 'Тип поля с номером',
                'choices' => [
                    'Номер в отдельном поле' => 0,
                    'Номер в начале' => 1,
                    'Номер в конце' => 2
                ],
                'expanded' => false,
                'multiple' => false,
                'placeholder' => false,
            ])
            ->add('num_number', IntegerNumberType::class, ['required' => false, 'label' => 'Поле с номером', 'attr' => ['maxLength' => 5]])
            ->add('num_price', IntegerNumberType::class, ['required' => false, 'label' => 'Поле с ценой', 'attr' => ['maxLength' => 5]])
            ->add('num_quantity', IntegerNumberType::class, ['required' => false, 'label' => 'Поле с количеством', 'attr' => ['maxLength' => 5]])
            ->add('num_summ', IntegerNumberType::class, ['required' => false, 'label' => 'Поле с суммой', 'attr' => ['maxLength' => 5]])
            ->add('num_gtd', IntegerNumberType::class, ['required' => false, 'label' => 'Поле с ГТД', 'attr' => ['maxLength' => 5]])
            ->add('num_country', IntegerNumberType::class, ['required' => false, 'label' => 'Поле со страной', 'attr' => ['maxLength' => 5]])
            ->add('priceadd', FloatNumberType::class, ['required' => false, 'label' => 'Коэффициент, умножаемый на цену'])
            ->add('num_number_razd', Type\TextType::class, ['required' => false, 'label' => 'Разделитель поля с номером', 'attr' => ['maxLength' => 2]])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

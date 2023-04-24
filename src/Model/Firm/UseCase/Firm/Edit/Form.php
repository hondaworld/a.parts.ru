<?php

namespace App\Model\Firm\UseCase\Firm\Edit;

use App\Form\Type\DatePickerType;
use App\ReadModel\Finance\NalogFetcher;
use App\ReadModel\Manager\ManagerFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private $managerFetcher;

    private $nalogFetcher;

    public function __construct(ManagerFetcher $managerFetcher, NalogFetcher $nalogFetcher)
    {
        $this->managerFetcher = $managerFetcher;
        $this->nalogFetcher = $nalogFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name_short', Type\TextType::class, ['label' => 'Краткое наименование', 'attr' => ['maxLength' => 50]])
            ->add('name', Type\TextType::class, ['label' => 'Полное наименование'])
            ->add('dateofadded', DatePickerType::class, ['label' => 'Дата добавления'])
            ->add('dateofclosed', DatePickerType::class, ['label' => 'Дата закрытия'])
            ->add('inn', Type\TextType::class, ['required' => false, 'label' => 'ИНН', 'attr' => ['class' => 'js-convert-number', 'maxLength' => 12]])
            ->add('kpp', Type\TextType::class, ['required' => false, 'label' => 'КПП', 'attr' => ['class' => 'js-convert-number', 'maxLength' => 9]])
            ->add('okpo', Type\TextType::class, ['required' => false, 'label' => 'ОКПО', 'attr' => ['class' => 'js-convert-number', 'maxLength' => 8]])
            ->add('ogrn', Type\TextType::class, ['required' => false, 'label' => 'ОГРН', 'attr' => ['class' => 'js-convert-number', 'maxLength' => 15]])
            ->add('isNDS', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'Является плательщиком НДС', 'label_attr' => ['class' => 'switch-custom']])
            ->add('isUr', Type\CheckboxType::class, ['required' => false, 'type' => 'danger', 'label' => 'Является юридическим лицом', 'label_attr' => ['class' => 'switch-custom']])
            ->add('directorID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Руководитель',
                'choices' => array_flip($this->managerFetcher->assoc()),
                'expanded' => false,
                'multiple' => false
            ])
            ->add('buhgalterID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Главный бухгалтер',
                'choices' => array_flip($this->managerFetcher->assoc()),
                'expanded' => false,
                'multiple' => false
            ])
            ->add('nalogID', Type\ChoiceType::class, [
                'required' => true,
                'label' => 'Налоговая схема',
                'choices' => array_flip($this->nalogFetcher->assoc()),
                'expanded' => false,
                'multiple' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

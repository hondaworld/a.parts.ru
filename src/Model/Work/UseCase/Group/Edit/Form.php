<?php

namespace App\Model\Work\UseCase\Group\Edit;


use App\Form\Type\FloatNumberType;
use App\Form\Type\IntegerNumberType;
use App\Model\Work\Entity\Group\WorkGroup;
use App\ReadModel\Work\WorkCategoryFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private $workCategoryFetcher;

    public function __construct(WorkCategoryFetcher $workCategoryFetcher)
    {
        $this->workCategoryFetcher = $workCategoryFetcher;
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, ['label' => 'Наименование'])
            ->add('norma', FloatNumberType::class, ['label' => 'Нормо-час'])
            ->add('sort', IntegerNumberType::class, ['required' => false, 'label' => 'Сортировка'])
            ->add('isTO', Type\ChoiceType::class, [
                'label' => 'ТО',
                'choices' => array_flip(WorkGroup::TO),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => false,
            ])
            ->add('workCategoryID', Type\ChoiceType::class, [
                'label' => 'Категория',
                'choices' => array_flip($this->workCategoryFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

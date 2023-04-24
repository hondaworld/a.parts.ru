<?php

namespace App\Model\Auto\UseCase\WorkPeriod\Edit;


use App\Form\Type\FloatNumberType;
use App\ReadModel\Work\WorkGroupFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private WorkGroupFetcher $workGroupFetcher;

    public function __construct(WorkGroupFetcher $workGroupFetcher)
    {
        $this->workGroupFetcher = $workGroupFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, ['label' => 'Наименование'])
            ->add('norma', FloatNumberType::class, ['required' => false, 'label' => 'Нормо-час со скидкой'])
            ->add('groups', Type\ChoiceType::class, [
                'is_advanced' => false,
                'required' => false,
                'label' => false,
                'choices' => array_flip($this->workGroupFetcher->assocTO()),
                'choice_label' => function ($choice, $key, $value) use ($options) {
                    return '<br>';
                },
                'expanded' => true,
                'multiple' => true,
                'label_html' => true,
                'is_cols' => true,
                'label_attr' => ['class' => 'checkbox-custom checkbox-inline pb-2'],
            ])
            ->add('groups_dop', Type\ChoiceType::class, [
                'is_advanced' => true,
                'required' => false,
                'label' => false,
                'choices' => array_flip($this->workGroupFetcher->assocTO()),
                'choice_label' => function ($choice, $key, $value) use ($options) {
                    return "<div class='overflow-hidden' style='height: 18px;' title='$key'>$key</div>";
                },
                'expanded' => true,
                'multiple' => true,
                'label_html' => true,
                'is_cols' => true,
                'label_attr' => ['class' => 'checkbox-custom checkbox-inline pb-2'],
            ])
            ->add('groups_rec', Type\ChoiceType::class, [
                'is_advanced' => true,
                'required' => false,
                'label' => false,
                'choices' => array_flip($this->workGroupFetcher->assocTO()),
                'choice_label' => function ($choice, $key, $value) use ($options) {
                    return '<br>';
                },
                'expanded' => true,
                'multiple' => true,
                'label_html' => true,
                'is_cols' => true,
                'label_attr' => ['class' => 'checkbox-custom checkbox-inline pb-2'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

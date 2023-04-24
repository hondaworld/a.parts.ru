<?php

namespace App\Model\Contact\UseCase\Town\Edit;


use App\ReadModel\Contact\TownRegionFetcher;
use App\ReadModel\Contact\TownTypeFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private TownRegionFetcher $townRegionFetcher;
    private TownTypeFetcher $townTypeFetcher;

    public function __construct(TownRegionFetcher $townRegionFetcher, TownTypeFetcher $townTypeFetcher)
    {
        $this->townRegionFetcher = $townRegionFetcher;
        $this->townTypeFetcher = $townTypeFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name_short', Type\TextType::class, ['label' => 'Краткое наименование', 'attr' => ['maxLength' => 150]])
            ->add('name', Type\TextType::class, ['label' => 'Наименование', 'attr' => ['maxLength' => 150]])
            ->add('name_doc', Type\TextType::class, ['required' => false, 'label' => 'Наименование в документах', 'attr' => ['maxLength' => 150]])
            ->add('daysFromMoscow', Type\TextType::class, ['label' => 'Дней до Москвы', 'attr' => ['class' => 'js-convert-number']])
            ->add('regionID', Type\ChoiceType::class, ['label' => 'Регион', 'placeholder' => '', 'choices' => array_flip($this->townRegionFetcher->assoc($options['data']->country))])
            ->add('typeID', Type\ChoiceType::class, ['label' => 'Тип', 'placeholder' => '', 'choices' => array_flip($this->townTypeFetcher->assoc())])
            ->add('isFree', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'Бесплатная доставка', 'label_attr' => ['class' => 'switch-custom']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

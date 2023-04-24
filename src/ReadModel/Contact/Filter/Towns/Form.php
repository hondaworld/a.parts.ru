<?php


namespace App\ReadModel\Contact\Filter\Towns;


use App\Form\Type\InPageType;
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
            ->add('inPage', InPageType::class)
            ->add('name_short', Type\TextType::class, ['filter' => true])
            ->add('name', Type\TextType::class, ['filter' => true])
            ->add('regionID', Type\ChoiceType::class, ['filter' => true, 'placeholder' => '', 'choices' => array_flip($this->townRegionFetcher->assoc($options['data']->country))])
            ->add('typeID', Type\ChoiceType::class, ['filter' => true, 'placeholder' => '', 'choices' => array_flip($this->townTypeFetcher->assoc())])
            ->add('isFree', Type\ChoiceType::class, [
                'filter' => true,
                'placeholder' => '',
                'choices' => [
                    'Да' => true,
                    'Нет' => false,
                ]
            ])
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
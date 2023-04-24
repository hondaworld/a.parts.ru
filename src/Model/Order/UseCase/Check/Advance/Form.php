<?php

namespace App\Model\Order\UseCase\Check\Advance;


use App\ReadModel\Sklad\ZapSkladFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private $zapSkladFetcher;

    public function __construct(ZapSkladFetcher $zapSkladFetcher)
    {
        $this->zapSkladFetcher = $zapSkladFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('zapSkladID', Type\ChoiceType::class, [
                'required' => true,
                'label' => 'Местоположение кассы',
                'choices' => array_flip($this->zapSkladFetcher->assocByManager($options['data']->managerID)),
                'choice_label' => function ($choice, $key, $value) {
                    return 'Касса в ' . $key;
                },
                'placeholder' => ''
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

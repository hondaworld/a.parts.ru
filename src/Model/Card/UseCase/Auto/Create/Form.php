<?php

namespace App\Model\Card\UseCase\Auto\Create;


use App\ReadModel\Auto\AutoModelFetcher;
use App\ReadModel\Auto\MotoModelFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private AutoModelFetcher $autoModelFetcher;
    private MotoModelFetcher $motoModelFetcher;

    public function __construct(AutoModelFetcher $autoModelFetcher, MotoModelFetcher $motoModelFetcher)
    {
        $this->autoModelFetcher = $autoModelFetcher;
        $this->motoModelFetcher = $motoModelFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('year', Type\TextareaType::class, ['label' => 'Года'])
        ;

        if (!in_array($options['data']->zapCard->getShopType()->getId(), [5,7])) {
            $builder->add('auto_modelID', Type\ChoiceType::class, [
                'required' => true,
                'label' => 'Модели автомобилей',
                'choices' => array_flip($this->autoModelFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => ''
            ]);
        } else {
            $builder->add('moto_modelID', Type\ChoiceType::class, [
                'required' => true,
                'label' => 'Модели мотоциклов',
                'choices' => array_flip($this->motoModelFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => ''
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

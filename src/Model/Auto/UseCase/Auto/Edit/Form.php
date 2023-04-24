<?php

namespace App\Model\Auto\UseCase\Auto\Edit;


use App\Form\Type\IntegerNumberType;
use App\ReadModel\Auto\AutoModelFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private AutoModelFetcher $autoModelFetcher;

    public function __construct(AutoModelFetcher $autoModelFetcher)
    {

        $this->autoModelFetcher = $autoModelFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('vin', Type\TextType::class, ['required' => false, 'label' => 'VIN', 'attr' => ['maxLength' => 20]])
            ->add('number', Type\TextType::class, ['required' => false, 'label' => 'Регистрационный номер', 'attr' => ['maxLength' => 20]])
            ->add('year', IntegerNumberType::class, ['required' => false, 'label' => 'Год выпуска'])
            ->add('auto_modelID', Type\ChoiceType::class, [
                'required' => true,
                'label' => 'Модель',
                'choices' => array_flip($this->autoModelFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
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

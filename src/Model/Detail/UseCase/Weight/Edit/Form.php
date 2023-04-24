<?php

namespace App\Model\Detail\UseCase\Weight\Edit;

use App\Form\Type\FloatNumberType;
use App\ReadModel\Detail\CreaterFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private $createrFetcher;

    public function __construct(CreaterFetcher $createrFetcher)
    {

        $this->createrFetcher = $createrFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('weight', FloatNumberType::class, ['label' => 'Вес в кг'])
            ->add('weightIsReal', Type\CheckboxType::class, ['required' => false, 'type' => 'success', 'label' => 'Проверен', 'label_attr' => ['class' => 'switch-custom']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

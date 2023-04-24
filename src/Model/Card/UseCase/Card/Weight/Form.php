<?php

namespace App\Model\Card\UseCase\Card\Weight;


use App\Form\Type\FloatNumberType;
use App\ReadModel\Card\ZapGroupFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private ZapGroupFetcher $zapGroupFetcher;

    public function __construct(ZapGroupFetcher $zapGroupFetcher)
    {
        $this->zapGroupFetcher = $zapGroupFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('weight', FloatNumberType::class, ['required' => false, 'label' => 'Вес'])
            ->add('weightIsReal', Type\CheckboxType::class, ['required' => false, 'type' => 'success', 'label' => 'Вес проверен', 'label_attr' => ['class' => 'switch-custom']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

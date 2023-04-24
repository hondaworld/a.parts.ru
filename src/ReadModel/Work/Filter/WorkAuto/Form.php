<?php


namespace App\ReadModel\Work\Filter\WorkAuto;


use App\ReadModel\Auto\AutoMarkaFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private AutoMarkaFetcher $autoMarkaFetcher;

    public function __construct(AutoMarkaFetcher $autoMarkaFetcher)
    {
        $this->autoMarkaFetcher = $autoMarkaFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('auto_markaID', Type\ChoiceType::class, [
                'required' => false,
                'choices' => array_flip($this->autoMarkaFetcher->assoc()),
                'placeholder' => ''
            ]);
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
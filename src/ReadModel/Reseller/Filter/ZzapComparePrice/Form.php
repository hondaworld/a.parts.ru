<?php


namespace App\ReadModel\Reseller\Filter\ZzapComparePrice;


use App\ReadModel\Card\ZapCardAbcFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private ZapCardAbcFetcher $zapCardAbcFetcher;

    public function __construct(ZapCardAbcFetcher $zapCardAbcFetcher)
    {
        $this->zapCardAbcFetcher = $zapCardAbcFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('abc', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip(['blank' => 'Пусто'] + $this->zapCardAbcFetcher->assoc()),
                'attr' => [
                    'onchange' => 'changeAbc(this)'
                ],
                'placeholder' => '',
                'label' => 'ABC'
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
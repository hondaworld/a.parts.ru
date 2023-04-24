<?php


namespace App\ReadModel\Analytics\Filter\StatSale;


use App\Form\Type\DateIntervalPickerType;
use App\ReadModel\Sklad\ZapSkladFetcher;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private ZapSkladFetcher $zapSkladFetcher;

    public function __construct(ZapSkladFetcher $zapSkladFetcher)
    {
        $this->zapSkladFetcher = $zapSkladFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateofreport', DateIntervalPickerType::class, ['required' => false, 'label' => 'Промежуток дат'])
            ->add('zapSkladID', Type\ChoiceType::class, [
                    'required' => false,
                    'label' => 'Склад',
                    'filter' => false,
                    'choices' => array_flip($this->zapSkladFetcher->assoc()),
                    'placeholder' => 'Все склады']
            )
//            ->add('nelikvid', Type\ChoiceType::class, [
//                    'required' => false,
//                    'label' => 'Учитывать неликвид',
//                    'filter' => false,
//                    'choices' => [
//                        'Неликвид 1' => '1',
//                        'Неликвид 2' => '2'
//                    ],
//                    'placeholder' => '']
//            )
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
<?php

namespace App\Model\Order\UseCase\Site\Edit;

use App\Form\Type\FloatNumberType;
use App\ReadModel\Auto\AutoMarkaFetcher;
use App\ReadModel\Detail\CreaterFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private CreaterFetcher $createrFetcher;
    private AutoMarkaFetcher $autoMarkaFetcher;

    public function __construct(CreaterFetcher $createrFetcher, AutoMarkaFetcher $autoMarkaFetcher)
    {
        $this->createrFetcher = $createrFetcher;
        $this->autoMarkaFetcher = $autoMarkaFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name_short', Type\TextType::class, ['label' => 'Краткое наименование', 'attr' => ['maxLength' => 2]])
            ->add('name', Type\TextType::class, ['label' => 'Наименование'])
            ->add('url', Type\TextType::class, ['label' => 'Адрес сайта'])
            ->add('norma_price', FloatNumberType::class, ['required' => false, 'label' => 'Стоимость нормо-часа'])
            ->add('auto_marka', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Автомобили',
                'choices' => array_flip($this->autoMarkaFetcher->assoc()),
                'expanded' => false,
                'multiple' => true,
                'attr' => ['size' => 10]
            ])
            ->add('creaters', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Производители',
                'choices' => array_flip($this->createrFetcher->assoc()),
                'expanded' => false,
                'multiple' => true,
                'attr' => ['size' => 10]
            ])
            ->add('isSklad', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'Если есть складская деталь, то заказные не показывать', 'label_attr' => ['class' => 'checkbox-custom']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

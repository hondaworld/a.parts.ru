<?php

namespace App\Model\Sklad\UseCase\ZapSklad\Edit;


use App\ReadModel\User\OptFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private OptFetcher $optFetcher;

    public function __construct(OptFetcher $optFetcher)
    {
        $this->optFetcher = $optFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name_short', Type\TextType::class, ['label' => 'Краткое наименование', 'attr' => ['maxLength' => 25]])
            ->add('name', Type\TextType::class, ['label' => 'Наименование'])
            ->add('koef', Type\TextType::class, ['label' => 'Коэффициент', 'attr' => ['class' => 'js-convert-float']])
            ->add('isTorg', Type\CheckboxType::class, ['required' => false, 'label' => 'Торговый склад', 'label_attr' => ['class' => 'switch-custom'], 'help' => 'Возможна продажа с данного склада'])
            ->add('optID', Type\ChoiceType::class, ['required' => false, 'label' => 'Оптовый пакет', 'choices' => array_flip($this->optFetcher->assoc($options['data']->optID)), 'help' => 'Если указан, то все цены склада будут от этого оптового пакета'])
            ->add('isMain', Type\CheckboxType::class, ['required' => false, 'label' => 'Основной склад', 'label_attr' => ['class' => 'switch-custom']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

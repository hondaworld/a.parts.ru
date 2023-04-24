<?php

namespace App\Model\Auto\UseCase\Model\Edit;


use App\ReadModel\Card\ZapCategoryFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private $zapCategoryFetcher;

    public function __construct(ZapCategoryFetcher $zapCategoryFetcher)
    {
        $this->zapCategoryFetcher = $zapCategoryFetcher;
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, ['label' => 'Наименование'])
            ->add('name_rus', Type\TextType::class, ['label' => 'Наименование русское'])
            ->add('path', Type\TextType::class, ['required' => false, 'label' => 'Путь'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

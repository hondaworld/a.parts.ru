<?php

namespace App\Model\Detail\UseCase\Zamena\Upload1;

use App\Form\Type\CsvUploadType;
use App\ReadModel\Detail\CreaterFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private CreaterFetcher $createrFetcher;

    public function __construct(CreaterFetcher $createrFetcher)
    {
        $this->createrFetcher = $createrFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', CsvUploadType::class, [
                'label' => 'Файл с заменами',
                'attr' => [
                    'placeholder' => 'Выберите файл',
                    'help' => 'Первая колонка номер, вторая - замененный номер'
                ]
            ])
            ->add('createrID', Type\ChoiceType::class, [
                'label' => 'Производитель',
                'choices' => array_flip($this->createrFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
            ])
            ->add('createrID2', Type\ChoiceType::class, [
                'label' => 'Производитель замены',
                'choices' => array_flip($this->createrFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

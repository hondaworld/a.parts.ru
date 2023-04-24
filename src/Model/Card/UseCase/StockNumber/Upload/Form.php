<?php

namespace App\Model\Card\UseCase\StockNumber\Upload;

use App\ReadModel\Detail\CreaterFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class Form extends AbstractType
{
    private CreaterFetcher $createrFetcher;

    public function __construct(CreaterFetcher $createrFetcher) {
        $this->createrFetcher = $createrFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', Type\FileType::class, [
                'label' => 'Файл',
                'attr' => ['placeholder' => 'Выберите прайс-лист', 'data-toggle' => "custom-file-input"],
                'help' => 'Файл типа TXT, CSV',
                'constraints' => [
                    new Assert\File([
                        'mimeTypes' => ["text/plain", "text/csv", "application/csv"],
                        'mimeTypesMessage' => 'Файл должен быть csv'
                    ])
                ]
            ])
            ->add('createrID', Type\ChoiceType::class, [
                'label' => 'Производитель',
                'choices' => array_flip($this->createrFetcher->assoc()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
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

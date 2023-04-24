<?php

namespace App\Model\Provider\UseCase\Price\Upload;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', Type\FileType::class, [
                'attr' => ['placeholder' => 'Выберите прайс-лист', 'data-toggle' => "custom-file-input"],
                'help' => 'Файл типа TXT, CSV, XLS, XLSX',
                'constraints' => [
                    new Assert\File([
                        'mimeTypes' => ["text/plain", "text/csv", "application/csv", "application/vnd.ms-excel", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", "application/octet-stream"],
                        'mimeTypesMessage' => 'Файл должен быть csv или excel файлом'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

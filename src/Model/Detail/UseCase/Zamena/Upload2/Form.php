<?php

namespace App\Model\Detail\UseCase\Zamena\Upload2;

use App\Form\Type\CsvUploadType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', CsvUploadType::class, [
                'label' => 'Файл с заменами',
                'attr' => [
                    'placeholder' => 'Выберите файл',
                    'help' => 'Первая колонка производитель, вторая - номер, третья - производитель замены, четвертая - номер замены'
                ]
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

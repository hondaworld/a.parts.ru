<?php

namespace App\ReadModel\Reseller\UseCase\Zzap\ComparePrice;

use App\Form\Type\CsvUploadType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', CsvUploadType::class, [
                'label' => 'Файл с ценами других поставщиков',
                'attr' => [
                    'placeholder' => 'Выберите файл',
                ],
                'help' => 'Файл типа CSV'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

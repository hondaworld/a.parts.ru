<?php


namespace App\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CsvUploadType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'mapped' => false,
            'attr' => ['placeholder' => 'Выберите файл 1', 'data-toggle' => "custom-file-input"],
            'help' => 'Файл типа TXT, CSV',
            'constraints' => [
                new Assert\File([
                    'mimeTypes' => ["text/plain", "text/csv", "application/csv"],
                    'mimeTypesMessage' => 'Файл должен быть csv'
                ])
            ]
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (isset($options['attr'])) $view->vars['attr'] = $view->vars['attr'] + ['placeholder' => 'Выберите файл', 'data-toggle' => "custom-file-input"];
    }

    public function getParent(): string
    {
        return Type\FileType::class;
    }
}
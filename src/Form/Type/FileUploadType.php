<?php


namespace App\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraints as Assert;

class FileUploadType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'mapped' => false,
            'required' => false,
            'attr' => ['placeholder' => 'Выберите файл', 'data-toggle' => "custom-file-input"],
            'help' => 'Файл типа BMP, GIF, PNG, JPG, PDF размером до 2Мб',
            'delete_url' => '',
            'delete_params' => [],
            'delete_message' => '',
            'is_vertical' => false,
            'constraints' => [
                new Assert\File([
                    'maxSize' => '2048k',
                    'mimeTypes' => ["image/bmp", "image/x-png", "image/gif", "image/jpeg", "image/jpg", "image/png", "application/pdf"],
                    'mimeTypesMessage' => 'Тип файла не соответствует'
                ])
            ]
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $parentData = $form->getParent()->getData();

        $fileUrl = '';
        if (null !== $parentData) {
            $accessor = PropertyAccess::createPropertyAccessor();
            $fileUrl = $accessor->getValue($parentData, $view->vars['name']);
        }

        $view->vars['is_vertical'] = $options['is_vertical'] ?: '';
        $view->vars['delete_url'] = $options['delete_url'] ?: '';
        $view->vars['delete_params'] = $options['delete_params'] ?: [];
        $view->vars['delete_message'] = $options['delete_message'] ?: '';
        $view->vars['file_upload_url'] = $fileUrl;
        $view->vars['file_upload_block_id'] = 'file_upload_block_id_' . uniqid();
        if (isset($options['attr'])) $view->vars['attr'] = $view->vars['attr'] + ['placeholder' => 'Выберите файл', 'data-toggle' => "custom-file-input"];

    }

    public function getParent(): string
    {
        return Type\FileType::class;
    }
}
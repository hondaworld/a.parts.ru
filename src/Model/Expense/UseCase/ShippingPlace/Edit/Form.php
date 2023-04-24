<?php

namespace App\Model\Expense\UseCase\ShippingPlace\Edit;


use App\Form\Type\FileUploadType;
use App\Form\Type\FloatNumberType;
use App\Form\Type\IntegerNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number', IntegerNumberType::class, ['label' => 'Номер места'])
            ->add('length', IntegerNumberType::class, ['label' => 'Длина, см'])
            ->add('width', IntegerNumberType::class, ['label' => 'Ширина, см'])
            ->add('height', IntegerNumberType::class, ['label' => 'Высота, см'])
            ->add('weight', FloatNumberType::class, ['label' => 'Вес, кг'])
            ->add('photo1', FileUploadType::class, [
                'label' => 'Фото 1',
                'delete_url' => 'shippings.places.photo1.delete',
                'delete_params' => ['id' => $options['data']->shipping_placeID],
                'delete_message' => 'Вы уверены, что хотите удалить фото 1?',
                'is_vertical' => true
            ])
            ->add('photo2', FileUploadType::class, [
                'label' => 'Фото 2',
                'delete_url' => 'shippings.places.photo2.delete',
                'delete_params' => ['id' => $options['data']->shipping_placeID],
                'delete_message' => 'Вы уверены, что хотите удалить фото 2?',
                'is_vertical' => true
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

<?php

namespace App\Model\Expense\UseCase\Shipping\Attach;

use App\Form\Type\FileUploadType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nakladnaya', FileUploadType::class, [
                'label' => 'Накладная',
                'delete_url' => 'shippings.attach.delete',
                'delete_params' => ['id' => $options['data']->shippingID],
                'delete_message' => 'Вы уверены, что хотите удалить накладную?',
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

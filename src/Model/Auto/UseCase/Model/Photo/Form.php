<?php

namespace App\Model\Auto\UseCase\Model\Photo;

use App\Form\Type\ImageType;
use App\ReadModel\Firm\FirmFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private FirmFetcher $firmFetcher;

    public function __construct(FirmFetcher $firmFetcher)
    {
        $this->firmFetcher = $firmFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('photo', ImageType::class, [
                'label' => 'Фото',
                'delete_url' => 'auto.model.photo.delete',
                'delete_params' => ['auto_markaID' => $options['data']->autoMarkaID, 'id' => $options['data']->autoModelID],
                'delete_message' => 'Вы уверены, что хотите удалить фото?',
                'is_vertical' => false
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

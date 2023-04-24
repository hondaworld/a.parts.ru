<?php

namespace App\Model\Auto\UseCase\Generation\Photo;

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
                'delete_url' => 'auto.generation.photo.delete',
                'delete_params' => ['auto_modelID' => $options['data']->autoModelID, 'id' => $options['data']->autoGenerationID],
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

<?php

namespace App\Model\Auto\UseCase\MotoGroup\Edit;


use App\Form\Type\ImageType;
use App\ReadModel\Card\ZapCategoryFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private $zapCategoryFetcher;

    public function __construct(ZapCategoryFetcher $zapCategoryFetcher)
    {
        $this->zapCategoryFetcher = $zapCategoryFetcher;
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, ['label' => 'Наименование'])
            ->add('photo', ImageType::class, [
                'label' => 'Фото',
                'delete_url' => 'auto.moto.group.photo.delete',
                'delete_params' => ['id' => $options['data']->moto_groupID],
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

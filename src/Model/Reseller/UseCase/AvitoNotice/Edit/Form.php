<?php

namespace App\Model\Reseller\UseCase\AvitoNotice\Edit;

use App\Form\Type\AutocompleteSimpleType;
use App\Form\Type\IntegerNumberType;
use App\Model\Reseller\Entity\Avito\AvitoNotice;
use App\ReadModel\Reseller\AvitoNoticeFetcher;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private AvitoNoticeFetcher $avitoNoticeFetcher;

    public function __construct(AvitoNoticeFetcher $avitoNoticeFetcher)
    {
        $this->avitoNoticeFetcher = $avitoNoticeFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('avito_id', IntegerNumberType::class, ['required' => false, 'label' => 'Номер существующего объявления Авито'])
            ->add('contact_phone', Type\TextType::class, ['required' => false, 'label' => 'Номер телефона'])
            ->add('address', Type\TextareaType::class, ['label' => 'Адрес', 'attr' => ['maxLength' => 255]])
            ->add('title', Type\TextType::class, ['label' => 'Наименование', 'attr' => ['maxLength' => 50]])
            ->add('description', CKEditorType::class, ['label' => 'Описание', 'config_name' => 'small'])
            ->add('type_id', Type\ChoiceType::class, [
                'label' => 'Тип',
                'choices' => array_flip(AvitoNotice::TYPES),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
            ])
            ->add('image_urls', Type\TextareaType::class, ['required' => false, 'label' => 'Картинки'])
            ->add('make', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Марка',
                'choices' => array_flip($this->avitoNoticeFetcher->assocMakes()),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
            ])
            ->add('model', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Модель',
                'choices' => array_flip($options['data']->models),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
            ])
            ->add('generation', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Поколение',
                'choices' => array_flip($options['data']->generations),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
            ])
            ->add('modification', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Модификация',
                'choices' => array_flip($options['data']->modifications),
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

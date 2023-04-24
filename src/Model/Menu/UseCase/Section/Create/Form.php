<?php

namespace App\Model\Menu\UseCase\Section\Create;

use App\ReadModel\Menu\MenuActionFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, ['label' => 'Наименование'])
            ->add('icon', Type\TextType::class, ['required' => false, 'label' => 'Иконка', 'attr' => ['maxLength' => 100]])
            ->add('url', Type\TextType::class, ['required' => false, 'label' => 'Адрес страницы'])
            ->add('entity', Type\TextType::class, ['required' => false, 'label' => 'Сущность'])
            ->add('pattern', Type\TextType::class, ['required' => false, 'label' => 'Шаблон'])
            ->add('actions', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Операции',
                'label_html' => true,
                'label_attr' => ['class' => 'checkbox-custom checkbox-inline', 'label_html' => true],
                'choices' => array_flip(array_map(function($a) {
                    return '<i class="' . $a['icon'] . '"></i> ' . $a['name'];
                }, MenuActionFetcher::STANDART_ACTIONS)),
                'expanded' => true,
                'multiple' => true,
                'placeholder' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

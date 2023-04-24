<?php

namespace App\Model\Menu\UseCase\Section\Edit;

use App\ReadModel\DropDownList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
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
            ->add('parent', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Родитель',
                'choices' => $options['data']->dropDownList,
                'expanded' => false,
                'multiple' => false,
                'placeholder' => false,
                'choice_value' => ChoiceList::value($this, 'id'),
                'choice_label' => ChoiceList::label($this, 'name'),
                'choice_attr' => function (DropDownList $dropDownList) use ($options) {
                    return $options['data']->id == $dropDownList->item['id'] ? ['disabled' => 'disabled'] : [];
                }
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

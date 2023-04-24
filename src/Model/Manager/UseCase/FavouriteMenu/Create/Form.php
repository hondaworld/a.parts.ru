<?php

namespace App\Model\Manager\UseCase\FavouriteMenu\Create;

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
            ->add('name', Type\TextType::class, ['label' => 'Наименование', 'attr' => ['maxLength' => 30]])
            ->add('url', Type\TextType::class, ['required' => false, 'label' => 'Адрес страницы', 'help' => 'От корня сайта со слешом в начале'])
            ->add('menu_section_id', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Пункт меню',
                'choices' => $options['data']->dropDownList,
                'expanded' => false,
                'multiple' => false,
                'placeholder' => '',
                'choice_value' => ChoiceList::value($this, 'id'),
                'choice_label' => ChoiceList::label($this, 'name'),
                'choice_attr' => function (DropDownList $dropDownList) use ($options) {
                    return $dropDownList->item['url'] == '' ? ['disabled' => 'disabled'] : [];
                }
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

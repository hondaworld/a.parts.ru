<?php


namespace App\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AutocompleteUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', Type\HiddenType::class, [

            ])
            ->add('name', Type\TextType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'class' => 'js-autocomplete-user',
                    'data-url' => $options['url']
                ],
            ])
            ->add('contactID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Адрес',
                'choices' => array_keys($options['contacts']),
                'choice_value' => function ($value) {
                    return $value;
                },
                'choice_label' => function ($choice, $key, $value) use ($options) {
                    return $options['contacts'][$value];
                },
            ])
            ->add('beznalID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Реквизит',
                'choices' => array_keys($options['beznals']),
                'choice_value' => function ($value) {
                    return $value;
                },
                'choice_label' => function ($choice, $key, $value) use ($options) {
                    return $options['beznals'][$value];
                },
            ]);

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $id = $event->getData()['id'];
                if ($id == '') $event->setData(['name' => '']);
                $name = $event->getData()['name'];
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'url' => '',
            'contacts' => '',
            'beznals' => '',
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $parentData = $form->getParent()->getData();
    }

    public function getParent(): string
    {
        return Type\FormType::class;
    }
}
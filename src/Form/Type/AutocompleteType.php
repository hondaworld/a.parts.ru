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

class AutocompleteType extends AbstractType
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
                    'class' => 'js-autocomplete',
                    'data-url' => $options['url']
                ],
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
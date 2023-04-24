<?php


namespace App\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;

class DatePickerType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'widget' => 'single_text',
            'format' => 'dd.MM.yyyy',
            'html5' => false,
            'attr' => [
                'class' => 'js-datepicker',
                'placeholder' => false
            ],
        ]);
        $resolver->setDefined(['filter', 'title']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (isset($options['filter']) && $options['filter']) {
            $view->vars['required'] = false;
            $view->vars['attr']['class'] = isset($view->vars['attr']['class']) ? $view->vars['attr']['class'] . ' form-control-sm form-control-alt' : 'form-control-sm form-control-alt';
        }
        if (isset($options['title']) && $options['title']) {
            $view->vars['attr']['title'] = $options['title'];
        }
    }

    public function getParent(): string
    {
        return Type\DateType::class;
    }
}
<?php


namespace App\Form\Extension;


use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PasswordTypeExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [PasswordType::class];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(['is_generate']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (isset($options['is_generate']) && $options['is_generate']) {
            $view->vars['is_generate'] = $options['is_generate'];
            $view->vars['attr']['class'] = isset($view->vars['attr']['class']) ? $view->vars['attr']['class'] . ' ' : '' . 'd-inline-block w-auto';
        }
    }
}
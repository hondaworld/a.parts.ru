<?php


namespace App\Form\Extension;


use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;

class TextTypeExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [TextType::class];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(['filter']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $parentData = $form->getParent()->getData();

        if (isset($options['filter']) && $options['filter']) {
            if (null !== $parentData) {
                $accessor = PropertyAccess::createPropertyAccessor();
                $value = $accessor->getValue($parentData, $form->getName());
            }

            $view->vars['required'] = false;
            $view->vars['attr']['class'] = isset($view->vars['attr']['class']) ? $view->vars['attr']['class'] . ' form-control-sm form-control-alt' : 'form-control-sm form-control-alt';
//            if ($value) $view->vars['attr']['class'] .= ' is-valid';
        }
    }
}
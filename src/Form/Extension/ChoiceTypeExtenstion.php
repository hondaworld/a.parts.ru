<?php


namespace App\Form\Extension;


use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ChoiceTypeExtenstion extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [ChoiceType::class];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(['is_advanced', 'is_cols', 'cols', 'choice_data', 'filter']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (isset($options['is_advanced'])) {
            $view->vars['is_advanced'] = $options['is_advanced'];
        }

        if (isset($options['is_cols'])) {
            $view->vars['is_cols'] = $options['is_cols'];
        }

        if (isset($options['cols'])) {
            $view->vars['cols'] = $options['cols'];
        }

        if (isset($options['choice_data'])) {
            $view->vars['choice_data'] = $options['choice_data'];
        }

        $parentData = $form->getParent()->getData();

        if (isset($options['filter']) && $options['filter']) {
            $value = '';
            if (null !== $parentData) {
                $accessor = PropertyAccess::createPropertyAccessor();
                $value = $accessor->getValue($parentData, $form->getName());
            }

            $view->vars['required'] = false;
            $view->vars['attr']['class'] = $view->vars['attr']['class'] ?? 'form-control-sm form-control-alt';
//            if ($value) $view->vars['attr']['class'] .= ' is-valid';
        }
    }
}
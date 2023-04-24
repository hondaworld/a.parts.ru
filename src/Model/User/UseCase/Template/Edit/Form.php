<?php

namespace App\Model\User\UseCase\Template\Edit;


use App\ReadModel\User\TemplateGroupFetcher;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    private TemplateGroupFetcher $templateGroupFetcher;

    public function __construct(TemplateGroupFetcher $templateGroupFetcher)
    {
        $this->templateGroupFetcher = $templateGroupFetcher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, ['label' => 'Наименование'])
            ->add('subject', Type\TextType::class, ['label' => 'Тема', 'attr' => ['maxLength' => 50]])
            ->add('text', CKEditorType::class, ['label' => 'Текст запчасти'])
            ->add('templateGroupID', Type\ChoiceType::class, [
                'label' => 'Группа',
                'choices' => array_flip($this->templateGroupFetcher->assoc()),
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

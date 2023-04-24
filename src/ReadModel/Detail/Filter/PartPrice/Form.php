<?php


namespace App\ReadModel\Detail\Filter\PartPrice;


use App\ReadModel\User\OptFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class Form extends AbstractType
{
    private OptFetcher $optFetcher;
    private Security $security;

    public function __construct(OptFetcher $optFetcher, Security $security)
    {
        $this->optFetcher = $optFetcher;
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('number', Type\TextType::class, ['filter' => true, 'attr' => ['placeholder' => 'Номер детали']]);

        if ($this->security->isGranted('part_price_change_opt', 'PartPrice')) {
            $builder->add('optID', Type\ChoiceType::class, [
                'filter' => true,
                'choices' => array_flip($this->optFetcher->assoc()),
                'placeholder' => false,
                'attr' => [
                    'class' => 'custom-select custom-select-sm form-control-alt ml-1',
                    'onchange' => 'this.form.submit()'
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Filter::class,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}
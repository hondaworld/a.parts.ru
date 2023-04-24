<?php

namespace App\Model\User\UseCase\User\EmailPrice;

use App\Form\Type\AutocompleteType;
use App\Model\User\Entity\User\User;
use App\Model\User\UseCase\User\Town;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Form extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email_price', Type\TextType::class, ['required' => false, 'label' => 'E-mail для рассылки прайсов через запятую', 'help' => 'Если не указан, используется e-mail для уведомлений при условии, что он активирован'])
            ->add('zapSkladID', Type\ChoiceType::class, [
                'required' => false,
                'label' => 'Количество со складов',
                'choices' => array_flip(User::EMAIL_SEND_SKLADS),
                'placeholder' => false
                ])
            ->add('isPrice', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'Рассылать прайс по e-mail', 'label_attr' => ['class' => 'switch-custom'], 'help' => 'Рассылка прайс-листов для ОПТ4 и ОПТ5. Прайс-листы формируются в xls формате 2 раза в день (8-40, 17-40 часов). Если в "Количество со складов" указан конкретный склад, отсылается прайс-лист этого склада, иначе - со всех складов. Если указан "Новый прайс", отсылает он вместо обычного.'])
            ->add('isPriceSummary', Type\CheckboxType::class, ['required' => false, 'type' => 'primary', 'label' => 'Рассылать склейку прайсов по e-mail', 'label_attr' => ['class' => 'switch-custom'], 'help' => 'Рассылка прайс-листов склейки для ОПТ4 и ОПТ5. Прайс-листы формируются в csv формате 1 раз в день (10 часов).'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Command::class,
        ]);
    }
}

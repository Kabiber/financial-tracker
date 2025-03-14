<?php

namespace App\Form;

use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];

        $builder
            ->add('category', EntityType::class, [
                'label' => 'Категория',
                'class' => Category::class,
                'choice_label' => 'name',
                'placeholder' => 'Все категории',
                'required' => false,
                'query_builder' => function (\Doctrine\ORM\EntityRepository $er) use ($user) {
                    return $er->createQueryBuilder('c')
                        ->where('c.user = :user')
                        ->setParameter('user', $user)
                        ->orderBy('c.name', 'ASC');
                },
            ])
            ->add('isIncome', ChoiceType::class, [
                'label' => 'Тип',
                'required' => false,
                'placeholder' => 'Все типы', // Для ChoiceType placeholder работает
                'choices' => [
                    'Доход' => true,
                    'Расход' => false,
                ],
            ])
            ->add('startDate', DateType::class, [
                'label' => 'Дата с',
                'required' => false,
                'widget' => 'single_text', // Для корректного отображения как текстового поля
                'attr' => ['placeholder' => 'Выберите дату'], // Через attr
            ])
            ->add('endDate', DateType::class, [
                'label' => 'Дата по',
                'required' => false,
                'widget' => 'single_text',
                'attr' => ['placeholder' => 'Выберите дату'], // Через attr
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
        ]);

        $resolver->setRequired('user');
    }
}


//
//namespace App\Form;
//
//use App\Entity\Category;
//use Symfony\Bridge\Doctrine\Form\Type\EntityType;
//use Symfony\Component\Form\AbstractType;
//use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
//use Symfony\Component\Form\Extension\Core\Type\DateType;
//use Symfony\Component\Form\FormBuilderInterface;
//use Symfony\Component\OptionsResolver\OptionsResolver;
//
//class TransactionFilterType extends AbstractType
//{
//    public function buildForm(FormBuilderInterface $builder, array $options): void
//    {
//        $builder
//            ->add('category', EntityType::class, [
//                'label' => 'Категории',
//                'class' => Category::class,
//                'choice_label' => 'name',
//                'placeholder' => 'Все категории',
//                'required' => false,
//            ])
//            ->add('isIncome', ChoiceType::class, [
//                'label' => 'Тип',
//                'choices' => [
//                    'Все' => null,
//                    'Доходы' => true,
//                    'Расходы' => false,
//                ],
//                'required' => false,
//            ])
//            ->add('startDate', DateType::class, [
//                'label' => 'С',
//                'widget' => 'single_text',
//                'required' => false,
//            ])
//            ->add('endDate', DateType::class, [
//                'label' => 'По',
//                'widget' => 'single_text',
//                'required' => false,
//            ]);
//    }
//
//    public function configureOptions(OptionsResolver $resolver): void
//    {
//        $resolver->setDefaults([
//            'data_class' => null,
//            'method' => 'GET',
//            'csrf_protection' => false,
//        ]);
//    }
//}
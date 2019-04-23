<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title',
                TextType::class,
                [
                    'label'    => 'Заголовок новости',
                    'required' => true,
                    'attr'     => ['autocomplete' => 'off'],
                ])
            ->add('content',
                TextareaType::class,
                [
                    'label'    => 'Текст новости',
                    'required' => true,
                    'attr'     => ['rows' => '5'],
                ])
            ->add('imageFile', VichFileType::class,
                [
                    'label'    => 'Изображение новости',
                    'required' => false,
                    'attr'     => ['placeholder' => 'Выберите изображение'],
                ])
            ->add('tags', EntityType::class,
                [
                    'class'     => 'App:Tag',
                    'label'     => 'Доступные теги',
                    'multiple'  => true,
                    'required'  => false,
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}

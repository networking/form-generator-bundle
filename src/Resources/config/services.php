<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Networking\FormGeneratorBundle\Form\Type\LegendType;
use Networking\FormGeneratorBundle\Form\FormType;
use Networking\FormGeneratorBundle\Helper\FormHelper;
use Networking\FormGeneratorBundle\Controller\FormAdminController;
use Networking\FormGeneratorBundle\Admin\FormAdmin;
use Networking\FormGeneratorBundle\Twig\Components\FormPageContent;
use Networking\FormGeneratorBundle\Twig\Extension\FormHelperExtension;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $services->set(LegendType::class)
        ->public()
        ->tag('form.type', ['alias' => 'form_legend']);

    $services->set(FormType::class)
        ->public()
        ->tag('form.type', ['alias' => 'generated_form']);

    $services->set(FormHelper::class)
        ->autowire();

    $services->set(FormPageContent::class)
      ->autowire();

    $services->alias('networking_form_generator.helper.form', FormHelper::class)
        ->public();

    $services->set(FormAdminController::class)
        ->autowire()
        ->public()
        ->tag('controller.service_arguments');

    $services->set(FormAdmin::class)
        ->call('setSonataAnnotationReader', [service('networking_init_cms.annotation.reader')])
        ->call('setLanguages', ['%networking_init_cms.page.languages%'])
        ->call('setTemplates', [[
            'edit' => '@NetworkingFormGenerator/Admin/edit.html.twig',
            'show' => '@NetworkingFormGenerator/Admin/show.html.twig',
        ]])
        ->tag('sonata.admin', [
            'manager_type' => 'orm',
            'model_class' => '%networking_form_generator.form_class%',
            'controller' => 'Networking\InitCmsBundle\Controller\CRUDController',
            'translation_domain' => 'formGenerator',
            'label' => 'form.admin.menu_label',
            'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
            'persist_filters' => true,
        ]);

    $services->set(FormHelperExtension::class)
        ->arg('$pageClass', '%networking_init_cms.admin.page.class%')
        ->arg('$pageContentClass', '%networking_form_generator.page_content_class%')
        ->tag('twig.extension');
};

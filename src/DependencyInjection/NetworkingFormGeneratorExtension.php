<?php

declare(strict_types=1);

namespace Networking\FormGeneratorBundle\DependencyInjection;

use Networking\FormGeneratorBundle\Form\FormType;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class NetworkingFormGeneratorExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('networking_form_generator.from_email', $config['from_email']);
        $container->setParameter('networking_form_generator.form_class', $config['form_class']);
        $container->setParameter('networking_form_generator.form_field_class', $config['form_field_class']);
        $container->setParameter('networking_form_generator.form_data_class', $config['form_data_class']);
        $container->setParameter('networking_form_generator.form_field_data_class', $config['form_field_data_class']);
        $container->setParameter('networking_form_generator.page_content_class', $config['page_content_class']);
        $cssInputSizes = array_merge(FormType::FRONTEND_INPUT_SIZES, $config['frontend_css_input_sizes']);

        $container->setParameter('networking_form_generator.frontend_css_input_sizes', $cssInputSizes);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');
    }
}

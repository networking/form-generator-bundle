<?php

namespace Networking\FormGeneratorBundle\DependencyInjection;

use Networking\FormGeneratorBundle\Form\FormType;
use Networking\FormGeneratorBundle\Model\Form;
use Networking\FormGeneratorBundle\Model\FormData;
use Networking\FormGeneratorBundle\Model\FormField;
use Networking\FormGeneratorBundle\Model\FormFieldData;
use Networking\FormGeneratorBundle\Model\FormPageContent;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('networking_form_generator');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('from_email')->defaultValue('example@example.com')->end()
                ->scalarNode('form_class')->defaultValue(Form::class)->end()
                ->scalarNode('form_data_class')->defaultValue(FormData::class)->end()
                ->scalarNode('form_field_class')->defaultValue(FormField::class)->end()
                ->scalarNode('form_field_data_class')->defaultValue(FormFieldData::class)->end()
                ->scalarNode('page_content_class')->defaultValue(FormPageContent::class)->end()
                ->arrayNode('frontend_css_input_sizes')
                    ->addDefaultsIfNotSet()
                    ->children()
                    ->scalarNode('xs')->end()
                    ->scalarNode('x')->end()
                    ->scalarNode('m')->end()
                    ->scalarNode('l')->end()
                    ->scalarNode('xl')->end()
                    ->scalarNode('xxl')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}

<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {

    $containerConfigurator->extension('doctrine', [
        'orm' => [
            'entity_managers' => [
                'default' => [
                    'mappings' => [
                        'NetworkingFormGeneratorBundle' => [
                            'type' => 'attribute',
                            'prefix' => 'Networking\FormGeneratorBundle\Model',
                            'dir' => '%kernel.project_dir%/vendor/networking/form-generator-bundle/src/Model',
                        ],
                    ],
                ],
            ],
        ],
    ]);
};

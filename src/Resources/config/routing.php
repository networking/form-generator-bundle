<?php

use Networking\FormGeneratorBundle\Controller\FormAdminController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->import(FormAdminController::class, 'attribute')
        ->prefix('/admin/cms/forms/api');
};

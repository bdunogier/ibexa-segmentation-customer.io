<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\SegmentationCustomerIo\Symfony\DependencyInjection;

use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

final class IbexaSegmentationCustomerIoExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config/')
        );

        $loader->load('services/segmentation.yaml');
    }

    public function prepend(ContainerBuilder $container)
    {
        $this->prependFrameworkConfig($container);
        $this->prependTwigConfig($container);
    }

    private function prependTwigConfig(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('twig', [
            'globals' => ['cio_site_id' => '%env(CUSTOMER_IO_TRACKING_SITE_ID)%']
        ]);
    }
    private function prependFrameworkConfig(ContainerBuilder $container)
    {
        $configFile = __DIR__ . '/../Resources/config/framework.yaml';
        $config = Yaml::parse(file_get_contents($configFile));
        $container->prependExtensionConfig('framework', $config);
        $container->addResource(new FileResource($configFile));
    }
}

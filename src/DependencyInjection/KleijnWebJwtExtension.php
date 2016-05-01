<?php
/*
 * This file is part of the KleijnWeb\JwtBundle package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KleijnWeb\JwtBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author John Kleijn <john@kleijnweb.nl>
 */
class KleijnWebJwtExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $keysDefinition = new Definition('jwt.keys');
        $keysDefinition->setClass('ArrayObject');

        foreach ($config['keys'] as $keyId => $keyConfig) {

            $keyConfig['kid'] = $keyId;
            $keyDefinition    = new Definition('jwt.keys.' . $keyId);
            $keyDefinition->setClass('KleijnWeb\JwtBundle\Authenticator\JwtKey');

            if (isset($keyConfig['loader'])) {
                $keyConfig['loader'] = $container->getDefinition($keyConfig['loader']);;
            }
            $keyDefinition->addArgument($keyConfig);
            $keysDefinition->addMethodCall('append', [$keyDefinition]);
        }

        $container->getDefinition('jwt.authenticator')->addArgument($keysDefinition);

    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return "jwt";
    }
}

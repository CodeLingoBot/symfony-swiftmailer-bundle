<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\SwiftmailerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class contains the configuration information for the bundle.
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 *
 * @author Christophe Coevoet <stof@notk.org>
 */
class Configuration implements ConfigurationInterface
{
    private $debug;

    /**
     * @param bool $debug The kernel.debug value
     */
    public function __construct($debug)
    {
        $this->debug = (bool) $debug;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('swiftmailer');
        $rootNode = method_exists(TreeBuilder::class, 'getRootNode') ? $treeBuilder->getRootNode() : $treeBuilder->root('swiftmailer');

        $rootNode
            ->beforeNormalization()
                ->ifNull()
                ->thenEmptyArray()
            ->end()
            ->beforeNormalization()
                ->ifTrue(function ($v) {
                    return \is_array($v) && !\array_key_exists('mailers', $v) && !\array_key_exists('mailer', $v);
                })
                ->then(function ($v) {
                    $mailer = [];
                    foreach ($v as $key => $value) {
                        if ('default_mailer' == $key) {
                            continue;
                        }
                        $mailer[$key] = $v[$key];
                        unset($v[$key]);
                    }
                    $v['default_mailer'] = isset($v['default_mailer']) ? (string) $v['default_mailer'] : 'default';
                    $v['mailers'] = [$v['default_mailer'] => $mailer];

                    return $v;
                })
            ->end()
            ->children()
                ->scalarNode('default_mailer')->end()
                ->append($this->getMailersNode())
            ->end()
            ->fixXmlConfig('mailer')
        ;

        return $treeBuilder;
    }

    /**
     * @return ArrayNodeDefinition
     */
    
}

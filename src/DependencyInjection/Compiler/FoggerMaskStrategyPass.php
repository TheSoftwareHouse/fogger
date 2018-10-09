<?php

namespace App\DependencyInjection\Compiler;

use App\Fogger\Mask\MaskStrategyProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class FoggerMaskStrategyPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(MaskStrategyProvider::class)) {
            return;
        }

        $definition = $container->findDefinition(MaskStrategyProvider::class);
        $taggedServices = $container->findTaggedServiceIds('fogger.mask');
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addMask', array(new Reference($id)));
        }
    }
}
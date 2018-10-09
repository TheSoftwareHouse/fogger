<?php

namespace App\DependencyInjection\Compiler;

use App\Fogger\Subset\SubsetStrategyProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FoggerSubsetStrategyPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(SubsetStrategyProvider::class)) {
            return;
        }

        $definition = $container->findDefinition(SubsetStrategyProvider::class);
        $taggedServices = $container->findTaggedServiceIds('fogger.subset');
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addSubsetStrategy', array(new Reference($id)));
        }
    }
}

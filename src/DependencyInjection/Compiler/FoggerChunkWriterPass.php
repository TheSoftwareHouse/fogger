<?php

namespace App\DependencyInjection\Compiler;

use App\Fogger\Data\Writer\ChunkWriterProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FoggerChunkWriterPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public function process(ContainerBuilder $container)
    {
        if (!$container->has(ChunkWriterProvider::class)) {
            return;
        }
        $definition = $container->findDefinition(ChunkWriterProvider::class);
        $writers = $this->findAndSortTaggedServices('fogger.writer', $container);

        foreach ($writers as $writer) {
            $definition->addMethodCall('addWriter', [$writer]);
        }
    }
}

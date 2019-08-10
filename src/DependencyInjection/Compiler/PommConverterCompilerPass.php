<?php

namespace App\DependencyInjection\Compiler;

use App\Database\Converter\DIConverter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class PommConverterCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(DIConverter::class)) {
            return;
        }

        $definition = $container->findDefinition(DIConverter::class);

        $taggedServices = $container->findTaggedServiceIds('app.pomm.converter');
        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall('addConverter', [
                    new Reference($id),
                    $attributes["alias"] ?? null
                ]);
            }
        }
    }
}
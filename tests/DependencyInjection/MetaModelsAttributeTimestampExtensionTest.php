<?php

/**
 * This file is part of MetaModels/attribute_timestamp.
 *
 * (c) 2012-2024 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/attribute_timestamp
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_timestamp/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\AttributeTimestampBundle\Test\DependencyInjection;

use MetaModels\AttributeTimestampBundle\DependencyInjection\MetaModelsAttributeTimestampExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * This test case test the extension.
 *
 * @covers \MetaModels\AttributeTimestampBundle\DependencyInjection\MetaModelsAttributeTimestampExtension
 *
 * @SuppressWarnings(PHPMD.LongClassName)
 */
class MetaModelsAttributeTimestampExtensionTest extends TestCase
{
    /**
     * Test that extension can be instantiated.
     *
     * @return void
     */
    public function testInstantiation()
    {
        $extension = new MetaModelsAttributeTimestampExtension();

        self::assertInstanceOf(MetaModelsAttributeTimestampExtension::class, $extension);
        self::assertInstanceOf(ExtensionInterface::class, $extension);
    }

    /**
     * Test that the services are loaded.
     *
     * @return void
     */
    public function testRegistersServices()
    {
        $container = new ContainerBuilder();

        $extension = new MetaModelsAttributeTimestampExtension();
        $extension->load([], $container);

        self::assertTrue($container->hasDefinition('metamodels.attribute_timestamp.factory'));
        $definition = $container->getDefinition('metamodels.attribute_timestamp.factory');
        self::assertCount(1, $definition->getTag('metamodels.attribute_factory'));
    }

    /**
     * Test that the services are loaded.
     *
     * @return void
     */
    public function testEventListenersAreRegistered()
    {
        $container = new ContainerBuilder();

        $extension = new MetaModelsAttributeTimestampExtension();
        $extension->load([], $container);

        // phpcs:disable
        self::assertTrue($container->hasDefinition('metamodels.attribute_timestamp.backend.encode_property_value_from_widget_listener'));
        $definition = $container->getDefinition('metamodels.attribute_timestamp.backend.encode_property_value_from_widget_listener');
        self::assertCount(1, $definition->getTag('kernel.event_listener'));

        self::assertTrue($container->hasDefinition('metamodels.attribute_timestamp.backend.decode_property_value_for_widget_listener'));
        $definition = $container->getDefinition('metamodels.attribute_timestamp.backend.decode_property_value_for_widget_listener');
        self::assertCount(1, $definition->getTag('handleDecodePropertyValueForWidgetEvent'));
        // phpcs:enable
    }
}

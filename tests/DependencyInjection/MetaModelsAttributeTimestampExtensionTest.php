<?php

/**
 * This file is part of MetaModels/attribute_timestamp.
 *
 * (c) 2012-2021 The MetaModels team.
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
 * @copyright  2012-2021 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_timestamp/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types = 1);

namespace MetaModels\AttributeTimestampBundle\Test\DependencyInjection;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use MetaModels\AttributeTimestampBundle\Attribute\AttributeTypeFactory;
use MetaModels\AttributeTimestampBundle\DependencyInjection\MetaModelsAttributeTimestampExtension;
use MetaModels\AttributeTimestampBundle\EventListener\BootListener;
use MetaModels\AttributeTimestampBundle\Migration\AllowNullMigration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * This test case test the extension.
 *
 * @covers \MetaModels\AttributeTimestampBundle\DependencyInjection\MetaModelsAttributeTimestampExtension
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
        $container = $this->getMockBuilder(ContainerBuilder::class)->getMock();

        $container
            ->expects(self::exactly(4))
            ->method('setDefinition')
            ->withConsecutive(
                [
                    'metamodels.attribute_timestamp.factory',
                    self::callback(
                        function ($value) {
                            /** @var Definition $value */
                            $this->assertInstanceOf(Definition::class, $value);
                            $this->assertEquals(AttributeTypeFactory::class, $value->getClass());
                            $this->assertCount(1, $value->getTag('metamodels.attribute_factory'));

                            return true;
                        }
                    )
                ],
                [
                    self::anything(),
                    self::anything(),
                ],
                [
                    self::anything(),
                    self::anything(),
                ],
                [
                    AllowNullMigration::class,
                    self::callback(
                        function ($value) {
                            /** @var Definition $value */
                            $this->assertInstanceOf(Definition::class, $value);
                            $this->assertCount(1, $value->getTag('contao.migration'));

                            return true;
                        }
                    )
                ]
            );

        $extension = new MetaModelsAttributeTimestampExtension();
        $extension->load([], $container);
    }

    /**
     * Test that the services are loaded.
     *
     * @return void
     */
    public function testEventListenersAreRegistered()
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)->getMock();

        $container
            ->expects(self::exactly(4))
            ->method('setDefinition')
            ->withConsecutive(
                [
                    self::anything(),
                    self::anything(),
                ],
                [
                    'metamodels.attribute_timestamp.backend.encode_property_value_from_widget_listener',
                    self::callback(
                        function ($value) {
                            /** @var Definition $value */
                            $this->assertInstanceOf(Definition::class, $value);
                            $this->assertEquals(BootListener::class, $value->getClass());
                            $this->assertCount(1, $value->getTag('kernel.event_listener'));
                            $this->assertEventListener(
                                $value,
                                EncodePropertyValueFromWidgetEvent::NAME,
                                'handleEncodePropertyValueFromWidget'
                            );

                            return true;
                        }
                    )
                ],
                [
                    'metamodels.attribute_timestamp.backend.decode_property_value_for_widget_listener',
                    self::callback(
                        function ($value) {
                            /** @var Definition $value */
                            $this->assertInstanceOf(Definition::class, $value);
                            $this->assertEquals(BootListener::class, $value->getClass());
                            $this->assertEventListener(
                                $value,
                                DecodePropertyValueForWidgetEvent::NAME,
                                'handleDecodePropertyValueForWidgetEvent'
                            );

                            return true;
                        }
                    )
                ]
            );

        $extension = new MetaModelsAttributeTimestampExtension();
        $extension->load([], $container);
    }

    /**
     * Assert that a definition is registered as event listener.
     *
     * @param Definition $definition The definition.
     * @param string     $eventName  The event name.
     * @param string     $methodName The method name.
     *
     * @return void
     */
    private function assertEventListener(Definition $definition, $eventName, $methodName)
    {
        self::assertCount(1, $definition->getTag('kernel.event_listener'));
        self::assertArrayHasKey(0, $definition->getTag('kernel.event_listener'));
        self::assertArrayHasKey('event', $definition->getTag('kernel.event_listener')[0]);
        self::assertArrayHasKey('method', $definition->getTag('kernel.event_listener')[0]);

        self::assertEquals($eventName, $definition->getTag('kernel.event_listener')[0]['event']);
        self::assertEquals($methodName, $definition->getTag('kernel.event_listener')[0]['method']);
    }
}

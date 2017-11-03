<?php

/**
 * * This file is part of MetaModels/attribute_timestamp.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage AttributeTimestamp
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_text/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\AttributeTimestampBundle\Test\DependencyInjection;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use MetaModels\AttributeTimestampBundle\Attribute\AttributeTypeFactory;
use MetaModels\AttributeTimestampBundle\EventListener\BackendEventListener;
use MetaModels\AttributeTimestampBundle\DependencyInjection\MetaModelsAttributeTimestampExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * This test case test the extension.
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

        $this->assertInstanceOf(MetaModelsAttributeTimestampExtension::class, $extension);
        $this->assertInstanceOf(ExtensionInterface::class, $extension);
    }

    /**
     * Test that the services are loaded.
     *
     * @return void
     */
    public function testFactoryIsRegistered()
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)->getMock();

        $container
            ->expects($this->exactly(3))
            ->method('setDefinition')
            ->withConsecutive(
                [
                    'metamodels.attribute_timestamp.factory',
                    $this->callback(
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
                    $this->anything(),
                    $this->anything(),
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
            ->expects($this->exactly(3))
            ->method('setDefinition')
            ->withConsecutive(
                [
                    $this->anything(),
                    $this->anything(),
                ],
                [
                    'metamodels.attribute_timestamp.backend.encode_property_value_from_widget_listener',
                    $this->callback(
                        function ($value) {
                            /** @var Definition $value */
                            $this->assertInstanceOf(Definition::class, $value);
                            $this->assertEquals(BackendEventListener::class, $value->getClass());
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
                    $this->callback(
                        function ($value) {
                            /** @var Definition $value */
                            $this->assertInstanceOf(Definition::class, $value);
                            $this->assertEquals(BackendEventListener::class, $value->getClass());
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
        $this->assertCount(1, $definition->getTag('kernel.event_listener'));
        $this->assertArrayHasKey(0, $definition->getTag('kernel.event_listener'));
        $this->assertArrayHasKey('event', $definition->getTag('kernel.event_listener')[0]);
        $this->assertArrayHasKey('method', $definition->getTag('kernel.event_listener')[0]);

        $this->assertEquals($eventName, $definition->getTag('kernel.event_listener')[0]['event']);
        $this->assertEquals($methodName, $definition->getTag('kernel.event_listener')[0]['method']);
    }
}

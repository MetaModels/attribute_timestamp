<?php

/**
 * This file is part of MetaModels/attribute_timestamp.
 *
 * (c) 2012-2016 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Tests
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2016 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_timestamp/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Attribute\Timestamp\Test;

use ContaoCommunityAlliance\Contao\Bindings\Events\Date\ParseDateEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use Doctrine\DBAL\Connection;
use MetaModels\Attribute\IAttribute;
use MetaModels\Attribute\Timestamp\BackendSubscriber;
use MetaModels\Attribute\Timestamp\Timestamp;
use MetaModels\Helper\TableManipulator;
use MetaModels\IMetaModelsServiceContainer;
use MetaModels\DcGeneral\Data\Model;
use PHPUnit\Framework\TestCase;

/**
 * This class tests the BackendSubscriber class.
 */
class BackendSubscriberTest extends TestCase
{
    /**
     * The backend subscriber being tested.
     *
     * @var BackendSubscriber
     */
    private $backendSubscriber;

    private $metaModel;

    private $item;

    private $eventDispatcher;

    /**
     * System columns.
     *
     * @var array
     */
    private $systemColumns = [
        'id',
        'pid',
        'sorting',
        'tstamp',
        'vargroup',
        'varbase ',
    ];

    /**
     * Create a mock.
     *
     * @param string     $className            The class which is mocked.
     * @param array|null $methods              Limit the methods.
     * @param array|null $constructorArguments Constructor arguments.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMock($className, array $methods = null, array $constructorArguments = null)
    {
        $builder = $this->getMockBuilder($className);

        if ($methods !== null) {
            $builder->setMethods($methods);
        }

        if ($constructorArguments) {
            $builder->setConstructorArgs($constructorArguments);
        }

        return $builder->getMock();
    }

    /**
     * Setup the test.
     *
     * @return void
     */
    public function setUp()
    {
        $this->eventDispatcher   = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->backendSubscriber = new BackendSubscriber();
        $this->metaModel         = $this->getMock('MetaModels\IMetaModel');
        $this->item              = $this->getMock('MetaModels\IItem', [], array($this->metaModel));
    }

    /**
     * Mock the service container.
     *
     * @return IMetaModelsServiceContainer
     */
    private function mockServiceContainer()
    {
        $serviceContainer = $this->getMock('MetaModels\IMetaModelsServiceContainer', array(), array());

        $serviceContainer
            ->expects($this->any())
            ->method('getEventDispatcher')
            ->will($this->returnValue($this->eventDispatcher));

        return $serviceContainer;
    }

    /**
     * Mock the environment.
     *
     * @return EnvironmentInterface
     */
    private function mockEnvironment()
    {
        $environment = $this->getMock('ContaoCommunityAlliance\DcGeneral\EnvironmentInterface', array(), array());

        $environment
            ->expects($this->any())
            ->method('getEventDispatcher')
            ->will($this->returnValue($this->eventDispatcher));

        return $environment;
    }

    /**
     * Mock the model.
     *
     * @param IAttribute $attribute The model attribute.
     *
     * @return Model
     */
    private function mockModelWithAttribute($attribute)
    {
        $model = $this->getMockBuilder('MetaModels\DcGeneral\Data\Model')
            ->setConstructorArgs([[]])
            ->getMock();

        $model
            ->expects($this->any())
            ->method('getItem')
            ->will($this->returnValue($this->item));

        return $model;
    }

    /**
     * Mock the timestamp attribute.
     *
     * @param string $format  The format being used.
     * @param array  $methods Methods which should be mocked.
     *
     * @return Timestamp
     */
    private function mockAttribute($format, array $methods = [])
    {
        $connection = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manipulator = $this->getMockBuilder(TableManipulator::class)
            ->setConstructorArgs([$connection, $this->systemColumns])
            ->getMock();

        $attribute = $this->getMock(
            'MetaModels\Attribute\Timestamp\Timestamp',
            array_merge(['getDateTimeFormatString', 'getAttribute'], $methods),
            array(
                $this->metaModel,
                [],
                $connection,
                $manipulator
            )
        );

        $attribute
            ->expects($this->any())
            ->method('getDateTimeFormatString')
            ->will($this->returnValue($format));

        $this->item
            ->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($attribute));

        return $attribute;
    }

    /**
     * Test that the attribute can be instantiated.
     *
     * @return void
     * @test
     */
    public function it_is_initializable()
    {
        $subscriber = new BackendSubscriber();
        $this->assertInstanceOf('MetaModels\Attribute\Timestamp\BackendSubscriber', $subscriber);
    }

    /**
     * Provide the test sets.
     *
     * @return array
     */
    public function dataProvider()
    {
        return array(
            array(
                'format'   => 'd-m-Y',
                'value'    => '01-01-2000',
            ),
            array(
                'format'   => 'd-m-Y',
                'value'    => '15-11-1980',
            ),
            array(
                'format'   => 'd-m-Y H:i:s',
                'value'    => '15-11-1980 11:22:33',
            ),
            array(
                'format'   => 'H:i:s',
                'value'    => '11:22:33',
            ),
            array(
                'format'   => 'H:i',
                'value'    => '20:00',
            ),
        );
    }

    /**
     * The subscriber creates the date from a timestamp.
     *
     * @param string $format The given date format.
     * @param string $value  The given date example
     *
     * @dataProvider dataProvider
     * @test
     */
    public function it_parses_timestamp_for_widget($format, $value)
    {
        $valuesBag = $this->getMock('ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBagInterface', array(), array());

        // Attribute will return timestamp, create it.
        $dateTime  = \DateTime::createFromFormat($format, $value);
        $timestamp = $dateTime->getTimestamp();

        $attribute = $this->mockAttribute($format, ['valueToWidget']);
        $attribute
            ->expects($this->any())
            ->method('valueToWidget')
            ->will($this->returnValue($timestamp));

        $model = $this->mockModelWithAttribute($attribute);

        $event = new EncodePropertyValueFromWidgetEvent($this->mockEnvironment(), $model, $valuesBag);
        $event->setProperty('date');
        $event->setValue($value);

        $this->backendSubscriber->handleEncodePropertyValueFromWidget($event);

        $this->assertEquals($timestamp, $event->getValue());
    }

    /**
     * The subscriber creates the timestamp from the widget value.
     *
     * @param string $format The given date format.
     * @param string $value  The given date example
     *
     * @dataProvider dataProvider
     * @test
     */
    public function it_creates_timestamp_from_widget_value($format, $value)
    {
        $dateTime  = \DateTime::createFromFormat($format, $value);
        $timestamp = $dateTime->getTimestamp();

        $attribute = $this->mockAttribute($format, ['widgetToValue']);
        $attribute
            ->expects($this->any())
            ->method('widgetToValue')
            ->will($this->returnValue($timestamp));

        $model = $this->mockModelWithAttribute($attribute);

        $event = new DecodePropertyValueForWidgetEvent($this->mockEnvironment(), $model);
        $event->setProperty('date');
        $event->setValue($timestamp);

        $this->eventDispatcher
            ->expects($this->atLeastOnce())
            ->method('dispatch')
            ->with(
                $this->anything(),
                $this->callback(
                    function (ParseDateEvent $event) use ($value) {
                        $event->setResult($value);

                        return true;
                    }
                )
            );

        $this->backendSubscriber->handleDecodePropertyValueForWidgetEvent($event);

        $this->assertEquals($value, $event->getValue());
    }
}

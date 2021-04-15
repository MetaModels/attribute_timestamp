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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2021 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_timestamp/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeTimestampBundle\Test\EventListener;

use ContaoCommunityAlliance\Contao\Bindings\Events\Date\ParseDateEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBagInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use MetaModels\Attribute\IAttribute;
use MetaModels\AttributeTimestampBundle\Attribute\Timestamp;
use MetaModels\AttributeTimestampBundle\EventListener\BootListener;
use MetaModels\DcGeneral\Data\Model;
use MetaModels\IMetaModel;
use MetaModels\Item;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This class tests the BackendSubscriber class.
 *
 * @covers \MetaModels\AttributeTimestampBundle\EventListener\BootListener
 */
class BootListenerTest extends TestCase
{
    /**
     * The backend subscriber being tested.
     *
     * @var BootListener
     */
    private $bootSubscriber;

    private $metaModel;

    private $item;

    private $eventDispatcher;

    /**
     * Setup the test.
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->eventDispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);
        $this->bootSubscriber  = new BootListener();
        $this->metaModel       = $this->getMockForAbstractClass(IMetaModel::class);
        $this->item            =
            $this->getMockBuilder(Item::class)->setMethods([])->setConstructorArgs([$this->metaModel, []])->getMock();
    }

    /**
     * Mock the environment.
     *
     * @return EnvironmentInterface
     */
    private function mockEnvironment()
    {
        $environment = $this->getMockForAbstractClass(EnvironmentInterface::class);

        $environment
            ->expects(self::any())
            ->method('getEventDispatcher')
            ->willReturn($this->eventDispatcher);

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
        $model =
            $this->getMockBuilder(Model::class)->setMethods([])->setConstructorArgs([$this->item])->getMock();

        $model
            ->expects(self::any())
            ->method('getProperty')
            ->willReturn($attribute);

        $model
            ->expects(self::any())
            ->method('getItem')
            ->willReturn($this->item);

        return $model;
    }

    /**
     * Mock the timestamp attribute.
     *
     * @param string $format The format being used.
     *
     * @return Timestamp
     */
    private function mockAttribute($format)
    {
        $attribute = $this
            ->getMockBuilder(Timestamp::class)
            ->setMethods([])
            ->setConstructorArgs([$this->metaModel])
            ->disableOriginalConstructor()
            ->getMock();


        $attribute
            ->expects(self::any())
            ->method('getDateTimeFormatString')
            ->willReturn($format);

        $this->item
            ->expects(self::any())
            ->method('getAttribute')
            ->willReturn($attribute);

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
        $subscriber = new BootListener();
        self::assertInstanceOf(BootListener::class, $subscriber);
    }

    /**
     * Provide the test sets.
     *
     * @return array
     */
    public function dataProvider()
    {
        return [
            [
                'format' => 'd-m-Y',
                'value'  => '01-01-2000',
            ],
            [
                'format' => 'd-m-Y',
                'value'  => '15-11-1980',
            ],
            [
                'format' => 'd-m-Y H:i:s',
                'value'  => '15-11-1980 11:22:33',
            ],
            [
                'format' => 'H:i:s',
                'value'  => '11:22:33',
            ],
            [
                'format' => 'H:i',
                'value'  => '20:00',
            ],
        ];
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
        $valuesBag = $this->getMockForAbstractClass(PropertyValueBagInterface::class);

        // Attribute will return timestamp, create it.
        $dateTime  = \DateTime::createFromFormat($format, $value);
        $timestamp = $dateTime->getTimestamp();

        $attribute = $this->mockAttribute($format);
        $attribute
            ->expects(self::any())
            ->method('valueToWidget')
            ->willReturn($timestamp);

        $model = $this->mockModelWithAttribute($attribute);

        $event = new EncodePropertyValueFromWidgetEvent($this->mockEnvironment(), $model, $valuesBag);
        $event->setProperty('date');
        $event->setValue($value);

        $this->bootSubscriber->handleEncodePropertyValueFromWidget($event);

        self::assertEquals($timestamp, $event->getValue());
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

        $attribute = $this->mockAttribute($format);
        $attribute
            ->expects(self::any())
            ->method('widgetToValue')
            ->willReturn($timestamp);

        $model = $this->mockModelWithAttribute($attribute);

        $event = new DecodePropertyValueForWidgetEvent($this->mockEnvironment(), $model);
        $event->setProperty('date');
        $event->setValue($timestamp);

        $this->eventDispatcher
            ->expects(self::atLeastOnce())
            ->method('dispatch')
            ->willReturnCallback(
                function (ParseDateEvent $event, string $eventName) use (&$value) {
                    switch ($eventName) {
                        case 'contao.events.data.parse':
                            $event->setResult($value);
                        default:
                    }

                    return true;
                }
            );

        $this->bootSubscriber->handleDecodePropertyValueForWidgetEvent($event);

        self::assertEquals($value, $event->getValue());
    }
}

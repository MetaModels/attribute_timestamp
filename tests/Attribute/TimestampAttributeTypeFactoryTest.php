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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2021 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_timestamp/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeTimestampBundle\Test\Attribute;

use Doctrine\DBAL\Connection;
use MetaModels\Attribute\IAttributeTypeFactory;
use MetaModels\AttributeTimestampBundle\Attribute\AttributeTypeFactory;
use MetaModels\AttributeTimestampBundle\Attribute\Timestamp;
use MetaModels\Helper\TableManipulator;
use MetaModels\IMetaModel;
use MetaModels\MetaModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Test the attribute factory.
 *
 * @covers \MetaModels\AttributeTimestampBundle\Attribute\AttributeTypeFactory
 */
class TimestampAttributeTypeFactoryTest extends TestCase
{
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
     * Mock a MetaModel.
     *
     * @param string $tableName        The table name.
     * @param string $language         The language.
     * @param string $fallbackLanguage The fallback language.
     *
     * @return IMetaModel|MockObject
     */
    protected function mockMetaModel($tableName, $language, $fallbackLanguage)
    {
        $metaModel = $this->getMockForAbstractClass(IMetaModel::class);

        $metaModel
            ->expects(self::any())
            ->method('getTableName')
            ->willReturn($tableName);

        $metaModel
            ->expects(self::any())
            ->method('getActiveLanguage')
            ->willReturn($language);

        $metaModel
            ->expects(self::any())
            ->method('getFallbackLanguage')
            ->willReturn($fallbackLanguage);

        return $metaModel;
    }

    /**
     * Mock the database connection.
     *
     * @return MockObject|Connection
     */
    private function mockConnection()
    {
        return $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Mock the table manipulator.
     *
     * @param Connection $connection The database connection mock.
     *
     * @return TableManipulator|MockObject
     */
    private function mockTableManipulator(Connection $connection)
    {
        return $this->getMockBuilder(TableManipulator::class)
            ->setConstructorArgs([$connection, $this->systemColumns])
            ->getMock();
    }

    /**
     * Mock the event dispatcher.
     *
     * @return EventDispatcherInterface|MockObject
     */
    private function mockEventDispatcher()
    {
        return $this->getMockBuilder(EventDispatcherInterface::class)
            ->getMockForAbstractClass();
    }

    /**
     * Override the method to run the tests on the attribute factories to be tested.
     *
     * @return IAttributeTypeFactory[]
     */
    protected function getAttributeFactories()
    {
        $connection  = $this->mockConnection();
        $manipulator = $this->mockTableManipulator($connection);
        $dispatcher  = $this->mockEventDispatcher();

        return array(new AttributeTypeFactory($connection, $manipulator, $dispatcher));
    }

    /**
     * Test creation of a timestamp attribute.
     *
     * @return void
     */
    public function testCreateSelect()
    {
        $connection  = $this->mockConnection();
        $manipulator = $this->mockTableManipulator($connection);
        $dispatcher  = $this->mockEventDispatcher();
        $factory     = new AttributeTypeFactory($connection, $manipulator, $dispatcher);
        $values      = [];
        $attribute   = $factory->createInstance(
            $values,
            $this->mockMetaModel('mm_test', 'de', 'en')
        );

        self::assertInstanceOf(Timestamp::class, $attribute);

        foreach ($values as $key => $value) {
            self::assertEquals($value, $attribute->get($key), $key);
        }
    }
}

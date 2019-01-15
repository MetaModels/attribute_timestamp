<?php

/**
 * This file is part of MetaModels/attribute_timestamp.
 *
 * (c) 2012-2019 The MetaModels team.
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
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_timestamp/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeTimestampBundle\Attribute;

use Doctrine\DBAL\Connection;
use MetaModels\Attribute\AbstractSimpleAttributeTypeFactory;
use MetaModels\Helper\TableManipulator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Attribute type factory for timestamp attributes.
 */
class AttributeTypeFactory extends AbstractSimpleAttributeTypeFactory
{
    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Create a new instance.
     *
     * @param Connection               $connection       Database connection.
     * @param TableManipulator         $tableManipulator The table manipulator.
     * @param EventDispatcherInterface $dispatcher       The event dispatcher.
     */
    public function __construct(
        Connection $connection,
        TableManipulator $tableManipulator,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($connection, $tableManipulator);

        $this->typeName   = 'timestamp';
        $this->typeIcon   = 'bundles/metamodelsattributetimestamp/timestamp.png';
        $this->typeClass  = Timestamp::class;
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function createInstance($information, $metaModel)
    {
        return new Timestamp($metaModel, $information, $this->connection, $this->tableManipulator, $this->dispatcher);
    }
}

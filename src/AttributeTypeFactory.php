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
 * @subpackage AttributeTimestamp
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2016 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_timestamp/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Attribute\Timestamp;

use Doctrine\DBAL\Driver\Connection;
use MetaModels\Attribute\AbstractAttributeTypeFactory;
use MetaModels\Helper\TableManipulator;

/**
 * Attribute type factory for timestamp attributes.
 */
class AttributeTypeFactory extends AbstractAttributeTypeFactory
{
    /**
     * Database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * The table manipulator.
     *
     * @var TableManipulator
     */
    private $tableManipulator;

    /**
     * Create a new instance.
     *
     * @param Connection       $connection       Database connection;
     * @param TableManipulator $tableManipulator The table manipulator.
     */
    public function __construct(Connection $connection, TableManipulator $tableManipulator)
    {
        parent::__construct();

        $this->typeName  = 'timestamp';
        $this->typeIcon  = 'bundles/metamodelsattributetimestampbundle/timestamp.png';
        $this->typeClass = 'MetaModels\Attribute\Timestamp\Timestamp';

        $this->connection       = $connection;
        $this->tableManipulator = $tableManipulator;
    }

    /**
     * {@inheritdoc}
     */
    public function createInstance($information, $metaModel)
    {
        return new $this->typeClass($metaModel, $information, $this->connection, $this->tableManipulator);
    }
}

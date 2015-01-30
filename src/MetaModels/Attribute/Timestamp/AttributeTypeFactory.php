<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage AttributeTimestamp
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @copyright  The MetaModels team.
 * @license    LGPL-3+
 * @filesource
 */

namespace MetaModels\Attribute\Timestamp;

use MetaModels\Attribute\AbstractAttributeTypeFactory;

/**
 * Attribute type factory for timestamp attributes.
 */
class AttributeTypeFactory extends AbstractAttributeTypeFactory
{
    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        parent::__construct();
        $this->typeName  = 'timestamp';
        $this->typeIcon  = 'system/modules/metamodelsattribute_timestamp/html/timestamp.png';
        $this->typeClass = 'MetaModels\Attribute\Timestamp\Timestamp';
    }
}

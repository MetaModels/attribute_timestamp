<?php

/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage AttributeTimestamp
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * Table tl_metamodel_attribute
 */
$GLOBALS['TL_DCA']['tl_metamodel_rendersetting']['metapalettes']['timestamp extends default'] = array
(
	'timesettings' => array('timeformat')
);

$GLOBALS['TL_DCA']['tl_metamodel_rendersetting']['fields']['timeformat'] = array
(
	'label'              => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['timeformat'],
	'exclude'            => true,
	'inputType'          => 'text',
	'eval' => array
	(
		'tl_class'       => 'w50'
	)
);

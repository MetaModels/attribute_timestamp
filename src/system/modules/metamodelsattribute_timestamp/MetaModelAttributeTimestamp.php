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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * This is the MetaModelAttribute class for handling text fields.
 *
 * @package	   MetaModels
 * @subpackage AttributeTimestamp
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 */
class MetaModelAttributeTimestamp extends MetaModelAttributeNumeric
{
	public function getFieldDefinition($arrOverrides = array())
	{
		$strDateType = $this->get('timetype');

		$arrFieldDef                 = parent::getFieldDefinition($arrOverrides);
		$arrFieldDef['eval']['rgxp'] = empty($strDateType) ? 'date' : $strDateType;
		$arrFieldDef['eval']['datepicker'] = true;
		return $arrFieldDef;
	}

	public function getAttributeSettingNames()
	{
		return array_merge(parent::getAttributeSettingNames(), array(
			'timetype'
		));
	}
}
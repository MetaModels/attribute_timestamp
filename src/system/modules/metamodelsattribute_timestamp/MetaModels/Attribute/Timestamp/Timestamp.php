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

namespace MetaModels\Attribute\Timestamp;

use MetaModels\Attribute\Numeric\Numeric;
use MetaModels\Helper\ContaoController;
use MetaModels\Render\Template;
use MetaModels\Render\Setting\ISimple;

/**
 * This is the MetaModelAttribute class for handling text fields.
 *
 * @package	   MetaModels
 * @subpackage AttributeTimestamp
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 */
class Timestamp extends Numeric
{
	/**
	 * {@inheritDoc}
	 */
	public function getFieldDefinition($arrOverrides = array())
	{
		$strDateType                       = $this->get('timetype');
		$arrFieldDef                       = parent::getFieldDefinition($arrOverrides);
		$arrFieldDef['eval']['rgxp']       = empty($strDateType) ? 'date' : $strDateType;
		$arrFieldDef['eval']['datepicker'] = ($strDateType == 'time') ? false : true;

		return $arrFieldDef;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAttributeSettingNames()
	{
		return array_merge(parent::getAttributeSettingNames(), array(
			'timetype'
		));
	}

	/**
	 * {@inheritDoc}
	 */
	protected function prepareTemplate(Template $objTemplate, $arrRowData, $objSettings = null)
	{
		parent::prepareTemplate($objTemplate, $arrRowData, $objSettings);

		/** @var ISimple $objSettings */
		if ($objSettings->get('timeformat'))
		{
			$objTemplate->format = $objSettings->get('timeformat');
		}
		else
		{
			$strDateType   = $this->get('timetype');
			$strFormatName = (empty($strDateType) ? 'date' : $strDateType) . 'Format';
			if ($GLOBALS['objPage'] && $GLOBALS['objPage']->$strFormatName)
			{
				$objTemplate->format = $GLOBALS['objPage']->$strFormatName;
			}
			else
			{
				$objTemplate->format = $GLOBALS['TL_CONFIG'][$strFormatName];
			}
		}
		if ($objTemplate->raw !== null)
		{
			$objTemplate->parsedDate = ContaoController::getInstance()->parseDate($objTemplate->format, $objTemplate->raw);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function valueToWidget($varValue)
	{
		if ($varValue === null)
		{
			return '';
		}

		if ($varValue != 0)
		{
			return $varValue;
		}

		// We need to parse the 0 timestamp manually because the widget will display an empty string.
		if ($varValue === 0 || $varValue === '')
		{
			return '';
		}

		// Get the right format for the field.
		switch ($this->get('timetype'))
		{
			case 'time':
				$strDateType = $GLOBALS['TL_CONFIG']['timeFormat'];
				break;

			case 'date':
				$strDateType = $GLOBALS['TL_CONFIG']['dateFormat'];
				break;

			case 'datim':
				$strDateType = $GLOBALS['TL_CONFIG']['datimFormat'];
				break;

			default:
				return $varValue;
		}

		// Return the data.
		return date($strDateType, $varValue);
	}

	/**
	 * {@inheritdoc}
	 */
	public function widgetToValue($varValue, $intId)
	{
		// Check if we have some data.
		if($varValue === '')
		{
			return null;
		}

		// If numeric we have already a integer value.
		if(is_numeric($varValue))
		{
			return intval($varValue);
		}

		// Make a unix timestamp from the string.
		$date = new \DateTime($varValue);
		return $date->getTimestamp();
	}
}

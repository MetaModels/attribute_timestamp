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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModelAttributeTimestamp extends MetaModelAttributeNumeric
{
	public function getFieldDefinition($arrOverrides = array())
	{
		$strDateType = $this->get('timetype');

		$arrFieldDef                 = parent::getFieldDefinition($arrOverrides);

		$arrFieldDef['eval']['rgxp'] = empty($strDateType) ? 'date' : $strDateType;
		$arrFieldDef['eval']['datepicker'] = ($strDateType == 'time') ? false : true;

		return $arrFieldDef;
	}

	public function getAttributeSettingNames()
	{
		return array_merge(parent::getAttributeSettingNames(), array(
			'timetype'
		));
	}

	/**
	 * Prepare a template.
	 *
	 * @param MetaModelTemplate                $objTemplate The template being prepared.
	 *
	 * @param array                            $arrRowData  The row date of the item.
	 *
	 * @param IMetaModelRenderSettingAttribute $objSettings The render settings to use.
	 *
	 * @return void
	 */
	protected function prepareTemplate(MetaModelTemplate $objTemplate, $arrRowData, $objSettings = null)
	{
		parent::prepareTemplate($objTemplate, $arrRowData, $objSettings);

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
			$objTemplate->parsedDate = MetaModelController::getInstance()->parseDate($objTemplate->format, $objTemplate->raw);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function valueToWidget($varValue)
	{
		if ($varValue === null) return '';
		if ($varValue != 0) return $varValue;

		//we need to parse the 0 timestamp manually because the widget will display an empty string
		$strDateType = $this->get('timetype');

		if($strDateType == 'time')
		{
			return $varValue;
		}
		else
		{
			$strDateType = empty($strDateType) ? 'date' : $strDateType;
			$strDateType = ($strDateType == 'date')? $GLOBALS['TL_CONFIG']['dateFormat'] : $GLOBALS['TL_CONFIG']['datimFormat'];
			
			return date($strDateType, $varValue);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function widgetToValue($varValue, $intId)
	{
		return ($varValue === '')?  null : $varValue;
	}

}

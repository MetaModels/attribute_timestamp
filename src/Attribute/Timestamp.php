<?php

/**
 * This file is part of MetaModels/attribute_timestamp.
 *
 * (c) 2012-2024 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/attribute_timestamp
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     David Greminger <david.greminger@1up.io>
 * @author     David Maack <david.maack@arcor.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Henry Lamorski <henry.lamorski@mailbox.org>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_timestamp/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeTimestampBundle\Attribute;

use Contao\System;
use Contao\Config;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Date\ParseDateEvent;
use Doctrine\DBAL\Connection;
use MetaModels\AttributeNumericBundle\Attribute\Numeric;
use MetaModels\Helper\TableManipulator;
use MetaModels\IMetaModel;
use MetaModels\Render\Setting\ISimple;
use MetaModels\Render\Template;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This is the MetaModelAttribute class for handling text fields.
 */
class Timestamp extends Numeric
{
    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $dispatcher;

    /**
     * Instantiate an MetaModel attribute.
     *
     * Note that you should not use this directly but use the factory classes to instantiate attributes.
     *
     * @param IMetaModel                    $objMetaModel     The MetaModel instance this attribute belongs to.
     * @param array                         $arrData          The information array for the attribute.
     * @param Connection|null               $connection       The database connection.
     * @param TableManipulator|null         $tableManipulator Table manipulator instance.
     * @param EventDispatcherInterface|null $dispatcher       The event dispatcher.
     */
    public function __construct(
        IMetaModel $objMetaModel,
        array $arrData = [],
        Connection $connection = null,
        TableManipulator $tableManipulator = null,
        EventDispatcherInterface $dispatcher = null
    ) {
        parent::__construct($objMetaModel, $arrData, $connection, $tableManipulator);

        if (null === $dispatcher) {
            // @codingStandardsIgnoreStart
            @\trigger_error(
                'Table event dispatcher is missing. It has to be passed in the constructor. Fallback will be dropped.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $dispatcher = System::getContainer()->get('event_dispatcher');
            assert($dispatcher instanceof EventDispatcherInterface);
        }
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function getSQLDataType()
    {
        return 'bigint(10) NULL';
    }

    /**
     * {@inheritDoc}
     */
    public function getFieldDefinition($arrOverrides = [])
    {
        $strDateType                 = $this->getDateTimeType();
        $arrFieldDef                 = parent::getFieldDefinition($arrOverrides);
        $arrFieldDef['eval']['rgxp'] = $strDateType;

        // Adjustment for the Contao setting to the SQL length of 10.
        $arrFieldDef['eval']['maxlength'] = (\strlen(Config::get($strDateType . 'Format')) * 2);

        if (empty($arrFieldDef['eval']['readonly'])) {
            $arrFieldDef['eval']['datepicker'] = true;
            $arrFieldDef['eval']['tl_class']   = ($arrFieldDef['eval']['tl_class'] ?? '') . ' wizard';
        }
        $arrFieldDef['eval']['clear_datetime'] = ($arrOverrides['clear_datetime'] ?? null);

        return $arrFieldDef;
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributeSettingNames()
    {
        return \array_merge(
            parent::getAttributeSettingNames(),
            [
                'timetype',
                'clear_datetime'
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function prepareTemplate(Template $objTemplate, $arrRowData, $objSettings)
    {
        parent::prepareTemplate($objTemplate, $arrRowData, $objSettings);

        /** @var ISimple $objSettings */
        if (null !== ($timeFormat = $objSettings->get('timeformat'))) {
            $objTemplate->format = $timeFormat;
        } else {
            $objTemplate->format = $this->getDateTimeFormatString();
        }

        if (!empty($objTemplate->raw)) {
            $event = new ParseDateEvent($objTemplate->raw, $objTemplate->format);
            $this->dispatcher->dispatch($event, ContaoEvents::DATE_PARSE);
            $objTemplate->parsedDate = $event->getResult();
        } else {
            $objTemplate->parsedDate = null;
        }
    }

    /**
     * Retrieve the selected type or fallback to 'date' if none selected.
     *
     * @return string
     */
    public function getDateTimeType()
    {
        return $this->get('timetype') ?: 'date';
    }

    /**
     * Retrieve the selected type or fallback to 'date' if none selected.
     *
     * @return string
     */
    public function getDateTimeFormatString()
    {
        $format = $this->getDateTimeType() . 'Format';
        $page   = $this->getObjPage();
        if ($page && $page->$format) {
            return $page->$format;
        }

        return Config::get($format);
    }

    /**
     * {@inheritdoc}
     */
    public function widgetToValue($varValue, $itemId)
    {
        // Check if we have some data.
        if ($varValue === '') {
            return null;
        }

        // If numeric we have already a integer value.
        if (\is_numeric($varValue)) {
            return (int) $varValue;
        }

        // @deprecated Parsing of string times is deprecated. Use the EncodePropertyValueFromWidgetEvent of the DCG
        // instead.
        // Make a unix timestamp from the string.
        $date = new \DateTime($varValue);
        return $date->getTimestamp();
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterOptions($idList, $usedOnly, &$arrCount = null)
    {
        $format = $this->getDateTimeFormatString();
        return \array_map(
            function ($value) use ($format) {
                $event = new ParseDateEvent($value, $format);
                $this->dispatcher->dispatch($event, ContaoEvents::DATE_PARSE);

                return $event->getResult();
            },
            parent::getFilterOptions($idList, $usedOnly, $arrCount)
        );
    }

    /**
     * Returns the global page object (replacement for super globals access).
     *
     * @return mixed The global page object
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getObjPage()
    {
        return isset($GLOBALS['objPage']) ? $GLOBALS['objPage'] : null;
    }

    /**
     * {@inheritDoc}
     *
     * This is needed for compatibility with MySQL strict mode.
     */
    public function serializeData($value)
    {
        return $value === '' ? null : $value;
    }
}

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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_timestamp/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeTimestampBundle\EventListener;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Date\ParseDateEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use MetaModels\AttributeTimestampBundle\Attribute\Timestamp;
use MetaModels\DcGeneral\Data\Model;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Handles event operations for timestamp attributes.
 */
class BootListener
{
    /**
     * Encode an timestamp attribute value from a widget value.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The subscribed event.
     *
     * @return void
     */
    public function handleEncodePropertyValueFromWidget(EncodePropertyValueFromWidgetEvent $event)
    {
        $attribute = $this->getSupportedAttribute($event);
        if (!$attribute) {
            return;
        }

        $date = \DateTime::createFromFormat($attribute->getDateTimeFormatString(), $event->getValue());
        if (!$date) {
            return;
        }

        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        $properties = $dataDefinition->getPropertiesDefinition();
        $property   = $properties->getProperty($event->getProperty());
        $extra      = $property->getExtra();
        if (isset($extra['clear_datetime'])) {
            switch ($extra['clear_datetime']) {
                case 'time':
                    $date->setTime(0, 0, 0);
                    break;
                case 'date':
                    // 01/01/1970 start of UNIX time counting as timestamp.
                    $date->setDate(1970, 1, 1);
                    break;
                default:
            }
        }

        $event->setValue($date->getTimestamp());
    }

    /**
     * Decode a timestamp attribute value for a widget value.
     *
     * @param DecodePropertyValueForWidgetEvent $event The subscribed event.
     *
     * @return void
     */
    public function handleDecodePropertyValueForWidgetEvent(DecodePropertyValueForWidgetEvent $event)
    {
        $attribute = $this->getSupportedAttribute($event);
        if (!$attribute) {
            return;
        }

        $dispatcher = $event->getEnvironment()->getEventDispatcher();
        assert($dispatcher instanceof EventDispatcherInterface);
        $value = $event->getValue();

        if (\is_int($value)) {
            $dateEvent = new ParseDateEvent($value, $attribute->getDateTimeFormatString());
            $dispatcher->dispatch($dateEvent, ContaoEvents::DATE_PARSE);

            $event->setValue($dateEvent->getResult());
        }
    }

    /**
     * Get the supported attribute or null.
     *
     * @param EncodePropertyValueFromWidgetEvent|DecodePropertyValueForWidgetEvent $event The subscribed event.
     *
     * @return Timestamp|null
     */
    private function getSupportedAttribute($event)
    {
        $model = $event->getModel();

        // Not a metamodel model.
        if (!$model instanceof Model) {
            return null;
        }

        $property  = $event->getProperty();
        $attribute = $model->getItem()->getAttribute($property);

        if ($attribute instanceof Timestamp) {
            return $attribute;
        }

        return null;
    }
}

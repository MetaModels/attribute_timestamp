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
 * @author     David Greminger <david.greminger@1up.io>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_timestamp/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Test\Attribute\Timestamp;

use Contao\Config;
use Contao\TextField;
use MetaModels\Attribute\Timestamp\Timestamp;
use MetaModels\IMetaModel;
use MetaModels\MetaModel;
use Contao\System;
use Contao\Controller;
use Contao\BaseTemplate;
use Contao\Widget;
use Contao\Date;
use Contao\Validator;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests to test class Timestamp.
 */
class TimestampTest extends TestCase
{
    /**
     * The preserved timezone.
     *
     * @var string
     */
    private $timezone;

    /**
     * Preserve the timezone.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function setUp()
    {
        $this->timezone = \date_default_timezone_get();
        \date_default_timezone_set('GMT');

        if (!\defined('TL_MODE')) {
            \define('TL_MODE', 'BE');
            $this
                ->getMockBuilder(System::class)
                ->setMockClassName('System')
                ->setMethods(['import'])
                ->disableOriginalConstructor()
                ->getMock();
            $this
                ->getMockBuilder(Config::class)
                ->setMockClassName('Config')
                ->setMethods(['initialize', 'preload', 'markModified', 'save'])
                ->disableOriginalConstructor()
                ->getMock();

            \class_alias(Controller::class, 'Controller');
            try {
                \class_alias(BaseTemplate::class, 'BaseTemplate');
            } catch (\Exception $exception) {
                // BaseTemplate came available with Contao 3.3.
            }

            \class_alias(Widget::class, 'Widget');
            \class_alias(Date::class, 'Date');
            \class_alias(Validator::class, 'Validator');
            // Some error strings for the validator.
            $GLOBALS['TL_LANG']['ERR']['date']        = '%s';
            $GLOBALS['TL_LANG']['ERR']['invalidDate'] = '%s';
            $GLOBALS['TL_LANG']['ERR']['time']        = '%s';
            $GLOBALS['TL_LANG']['ERR']['dateTime']    = '%s';
        }
    }

    /**
     * Restore the timezone.
     *
     * @return void
     */
    protected function tearDown()
    {
        \date_default_timezone_set($this->timezone);
    }

    /**
     * Mock a MetaModel.
     *
     * @param string $language         The language.
     * @param string $fallbackLanguage The fallback language.
     *
     * @return IMetaModel
     */
    protected function mockMetaModel($language, $fallbackLanguage)
    {
        $metaModel = $this->getMockBuilder(MetaModel::class)->setMethods([])->setConstructorArgs([[]])->getMock();

        $metaModel
            ->expects($this->any())
            ->method('getTableName')
            ->will($this->returnValue('mm_unittest'));

        $metaModel
            ->expects($this->any())
            ->method('getActiveLanguage')
            ->will($this->returnValue($language));

        $metaModel
            ->expects($this->any())
            ->method('getFallbackLanguage')
            ->will($this->returnValue($fallbackLanguage));

        return $metaModel;
    }

    /**
     * Create the attribute with the given values.
     *
     * @param array           $data      The initialization array.
     *
     * @param null|IMetaModel $metaModel The MetaModel instance.
     *
     * @return Timestamp
     */
    protected function getAttribute($data, $metaModel = null)
    {
        return new Timestamp(
            $metaModel ?: $this->mockMetaModel('en', 'en'),
            \array_replace_recursive(
                [
                    'id'          => 1,
                    'pid'         => 1,
                    'tstamp'      => 0,
                    'name'        => [
                        'en' => 'name English',
                        'de' => 'name German',
                    ],
                    'description' => [
                        'en' => 'description English',
                        'de' => 'description German',
                    ],
                    'type'        => 'base',
                    'colname'     => 'timestamp',
                    'isvariant'   => 1,
                    // Settings originating from tl_metamodel_dcasetting.
                    'tl_class'    => 'custom_class',
                    'readonly'    => 1
                ],
                $data
            )
        );
    }

    /**
     * Test that the attribute can be instantiated.
     *
     * @return void
     */
    public function testInstantiation()
    {
        $attribute = new Timestamp($this->mockMetaModel('en', 'en'));
        $this->assertInstanceOf(Timestamp::class, $attribute);
    }

    /**
     * Provide the test sets.
     *
     * @return array
     */
    public function dataProvider()
    {
        return [
            [
                'type'   => 'date',
                'format' => 'd-m-Y',
                'value'  => '01-01-2000',
            ],
            [
                'type'   => 'date',
                'format' => 'd-m-Y',
                'value'  => '15-11-1980',
            ],
            [
                'type'   => 'datim',
                'format' => 'd-m-Y H:i:s',
                'value'  => '15-11-1980 11:22:33',
            ],
            [
                'type'   => 'time',
                'format' => 'H:i:s',
                'value'  => '11:22:33',
            ],
            [
                'type'   => 'time',
                'format' => 'H:i',
                'value'  => '20:00',
            ],
        ];
    }

    /**
     * Set a config value.
     *
     * @param string $key   The name of the value.
     *
     * @param mixed  $value The value.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function setConfigValue($key, $value)
    {
        if (!\in_array('set', \get_class_methods('Config'))) {
            $GLOBALS['TL_CONFIG'][$key] = $value;
        } else {
            Config::set($key, $value);
        }
    }

    /**
     * Test that the attribute can be instantiated.
     *
     * @param string $type   The date type.
     *
     * @param string $format The format string.
     *
     * @param string $value  The text value to use as post data.
     *
     * @return void
     *
     * @dataProvider dataProvider
     */
    public function testDateTime($type, $format, $value)
    {
        // Detect the widget bug and mark test skipped if encountered.
        if ($type === 'time') {
            try {
                $this->setConfigValue('timeFormat', $format);
                @TextField::getAttributesFromDca(['eval' => ['rgxp' => 'time']], 'test', '11:22:33');
            } catch (\OutOfBoundsException $exception) {
                $this->markTestSkipped('Widget bug detected? See https://github.com/contao/core/pull/7721');
                return;
            }
        }

        $attribute       = $this->getAttribute(['timetype' => $type]);
        $fieldDefinition = \array_replace_recursive(
            $attribute->getFieldDefinition(),
            [
                'eval'             => [
                    'submitOnChange' => false,
                    'allowHtml'      => false,
                    'rte'            => false,
                    'preserveTags'   => false,
                    'encrypt'        => false,
                    'nullIfEmpty'    => false
                ],
                'activeRecord'     => null,
                'options_callback' => null,
                'options'          => null,
            ]
        );
        $this->setConfigValue('dateFormat', 'd-m-Y');
        $this->setConfigValue('timeFormat', 'h:i');
        $this->setConfigValue('datimFormat', 'd-m-Y h:i');

        $this->setConfigValue($type . 'Format', $format);
        $this->setConfigValue('timeZone', 'GMT');

        $dateTime  = new \DateTime($value, new \DateTimeZone(\date_default_timezone_get()));
        $timeStamp = $dateTime->getTimestamp();
        $converted = $attribute->valueToWidget($timeStamp);

        $prepared = TextField::getAttributesFromDca(
            $fieldDefinition,
            'test',
            $value
        );

        $widget = $this
            ->getMockBuilder(TextField::class)
            ->setMethods(['getPost'])
            ->setConstructorArgs([$prepared])
            ->getMock();
        $widget->expects($this->any())->method('getPost')->will($this->returnValue($value));

        /** @var TextField $widget */
        $widget->validate();

        $text = $widget->value;
        $this->assertEquals($converted, $timeStamp);

        $converted = $attribute->widgetToValue($text, 1);
        $this->assertEquals(
            \date($format, $timeStamp),
            \date($format, $converted),
            \date('d-m-Y h:i', $timeStamp) . ' <> ' . \date('d-m-Y h:i', $converted)
        );
    }

    /**
     * Test that the date picker get's enabled when not read only.
     *
     * @return void
     */
    public function testEnableDatepickerWhenNotReadOnly()
    {
        $attribute  = $this->getAttribute(['timetype' => 'date', 'readonly' => 0]);
        $definition = $attribute->getFieldDefinition();

        $this->assertArrayHasKey('datepicker', $definition['eval']);
        $this->assertArrayHasKey('tl_class', $definition['eval']);
        $this->assertEquals(true, $definition['eval']['datepicker']);
        $this->assertEquals('custom_class wizard', $definition['eval']['tl_class']);
    }

    /**
     * Test that the date picker does not get enabled when read only.
     *
     * @return void
     */
    public function testDisableDatepickerWhenReadOnly()
    {
        $attribute  = $this->getAttribute(['timetype' => 'date', 'readonly' => 1]);
        $definition = $attribute->getFieldDefinition();

        $this->assertArrayNotHasKey('datepicker', $definition['eval']);
        $this->assertArrayHasKey('tl_class', $definition['eval']);
        $this->assertEquals('custom_class', $definition['eval']['tl_class']);
    }
}

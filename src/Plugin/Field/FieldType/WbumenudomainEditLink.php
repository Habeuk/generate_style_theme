<?php
namespace Drupal\wbumenudomain\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Provides a field type of baz.
 *
 * @FieldType(
 *   id = "wbumenudomaineditlink",
 *   label = @Translation("wbumenudomain edit link field"),
 *   default_formatter = "wbumenudomaineditlink",
 *   default_widget = "wbumenudomaineditlink",
 * )
 */
class WbumenudomainEditLink extends FieldItemBase
{

    /**
     *
     * {@inheritdoc}
     */
    public static function schema(FieldStorageDefinitionInterface $field_definition)
    {
        return [
            'columns' => array(
                'value' => array(
                    'type' => 'text',
                    'size' => 'normal',
                    'not null' => FALSE
                )
            )
        ];
    }

    /**
     *
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        $value = $this->get('value')->getValue();
        return $value === NULL || $value === '';
    }

    /**
     *
     * {@inheritdoc}
     */
    public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition)
    {
        $properties['value'] = DataDefinition::create('string')->setLabel(t('Hex value'));
        return $properties;
    }
}
<?php

namespace Pim\Component\Connector\ArrayConverter\Flat;

use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Pim\Component\Connector\Exception\ArrayConversionException;

/**
 * Category Flat Converter
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryStandardConverter implements StandardArrayConverterInterface
{
    /**
     * {@inheritdoc}
     *
     * Converts flat csv array to standard structured array:
     *
     * Before:
     * [
     *      'code'        => 'mycode',
     *      'parent'      => 'master',
     *      'label-fr_FR' => 'T-shirt super beau',
     *      'label-en_US' => 'T-shirt very beautiful',
     * ]
     *
     * After:
     * [
     *      'code'   => 'mycode',
     *      'parent' => 'master',
     *      'labels' => [
     *          'fr_FR' => 'T-shirt super beau',
     *          'en_US' => 'T-shirt very beautiful',
     *      ],
     * ]
     */
    public function convert(array $item, array $options = [])
    {
        $this->validate($item);

        $convertedItem = ['labels' => []];
        foreach ($item as $field => $data) {
            if ('' !== $data) {
                $convertedItem = $this->convertField($convertedItem, $field, $data);
            }
        }

        return $convertedItem;
    }

    /**
     * @param array  $convertedItem
     * @param string $field
     * @param mixed  $data
     *
     * @return array
     */
    protected function convertField($convertedItem, $field, $data)
    {
        if (false !== strpos($field, 'label-', 0)) {
            $labelTokens = explode('-', $field);
            $labelLocale = $labelTokens[1];
            $convertedItem['labels'][$labelLocale] = $data;
        } else {
            switch ($field) {
                case 'code':
                case 'parent':
                    $convertedItem[$field] = (string) $data;
                    break;
            }
        }

        return $convertedItem;
    }

    /**
     * @param array $item
     */
    protected function validate(array $item)
    {
        $this->validateRequiredFields($item, ['code']);
    }

    /**
     * @param array $item
     * @param array $requiredFields
     *
     * @throws ArrayConversionException
     */
    protected function validateRequiredFields(array $item, array $requiredFields)
    {
        foreach ($requiredFields as $requiredField) {
            if (!in_array($requiredField, array_keys($item))) {
                throw new ArrayConversionException(
                    sprintf(
                        'Field "%s" is expected, provided fields are "%s"',
                        $requiredField,
                        implode(', ', array_keys($item))
                    )
                );
            }

            if ('' === $item[$requiredField]) {
                throw new ArrayConversionException(
                    sprintf(
                        'Field "%s" must be filled',
                        $requiredField,
                        implode(', ', array_keys($item))
                    )
                );
            }
        }
    }
}

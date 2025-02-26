<?php

namespace FelixNagel\T3extblog\Utility;

/**
 * This file is part of the "t3extblog" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * TypoScript Utility class.
 *
 * Taken from Georg Ringer's news extension
 */
class TypoScript
{
    public function override(array $base, array $overload): array
    {
        $validFields = GeneralUtility::trimExplode(',', $overload['settings']['overrideFlexformSettingsIfEmpty'], true);
        foreach ($validFields as $fieldName) {
            // Multilevel field
            if (strpos($fieldName, '.') !== false) {
                $keyAsArray = explode('.', $fieldName);

                $foundInCurrentTs = $this->getValue($base, $keyAsArray);
                if (is_string($foundInCurrentTs) && $foundInCurrentTs === '') {
                    $foundInOriginal = $this->getValue($overload['settings'], $keyAsArray);
                    if ($foundInOriginal) {
                        $base = $this->setValue($base, $keyAsArray, $foundInOriginal);
                    }
                }
            } elseif ((!isset($base[$fieldName]) || ($base[$fieldName] === ''))
                && isset($overload['settings'][$fieldName])) {
                $base[$fieldName] = $overload['settings'][$fieldName];
            }
        }

        return $base;
    }

    /**
     * Get value from array by path.
     *
     * @SuppressWarnings("PHPMD.CountInLoopExpression")
     *
     */
    protected function getValue(array $data, array $path)
    {
        $found = true;
        $pathCount = count($path);

        for ($x = 0; ($x < $pathCount && $found); ++$x) {
            $key = $path[$x];

            if (isset($data[$key])) {
                $data = $data[$key];
            } else {
                $found = false;
            }
        }

        if ($found) {
            return $data;
        }

        return null;
    }

    /**
     * Set value in array by path.
     *
     */
    protected function setValue(array $array, $path, $value): array
    {
        $this->setValueByReference($array, $path, $value);

        return array_merge_recursive([], $array);
    }

    /**
     * Set value by reference.
     *
     * @SuppressWarnings("PHPMD.CountInLoopExpression")
     *
     */
    private function setValueByReference(array &$array, array $path, $value)
    {
        while (count($path) > 1) {
            $key = array_shift($path);
            if (!isset($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $key = reset($path);
        $array[$key] = $value;
    }
}

<?php

/**
 * This file is part of the "repeatable_form_elements" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

/*
 * This file is part of the "repeatable_form_elements" TYPO3 CMS extension.
 *
 * (c) 2018-2026 Konrad Michalik <km@move-elevator.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TRITUM\RepeatableFormElements\Finisher;

use DateTimeInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;

use function is_array;
use function is_int;
use function is_string;

/**
 * SaveToDatabaseFinisher.
 *
 * @author Konrad Michalik <km@move-elevator.de>
 */
class SaveToDatabaseFinisher extends \TYPO3\CMS\Form\Domain\Finishers\SaveToDatabaseFinisher
{
    public function __construct(private readonly ConnectionPool $connectionPool) {}

    protected function process(int $iterationCount): void
    {
        $this->throwExceptionOnInconsistentConfiguration();

        $table = $this->parseOption('table');
        $table = is_string($table) ? $table : '';
        $elementsConfiguration = $this->parseOption('elements');
        $elementsConfiguration = is_array($elementsConfiguration) ? $elementsConfiguration : [];
        $databaseColumnMappingsConfiguration = $this->parseOption('databaseColumnMappings');

        $this->databaseConnection = $this->connectionPool->getConnectionForTable($table);

        $databaseData = [];
        $databaseColumnMappingsConfiguration = is_array($databaseColumnMappingsConfiguration) ? $databaseColumnMappingsConfiguration : [];
        foreach ($databaseColumnMappingsConfiguration as $databaseColumnName => $databaseColumnConfiguration) {
            $value = $this->parseOption('databaseColumnMappings.'.$databaseColumnName.'.value');
            if (
                (null === $value || '' === $value)
                && ($databaseColumnConfiguration['skipIfValueIsEmpty'] ?? false) === true
            ) {
                continue;
            }

            $databaseData[$databaseColumnName] = $value;
        }

        // decide which strategy to use
        $containerConfiguration = $this->parseOption('container');
        if (is_string($containerConfiguration) && '' !== $containerConfiguration) {
            $this->processContainer($containerConfiguration, $elementsConfiguration, $databaseData, $table, $iterationCount);
        } else {
            $databaseData = $this->prepareData($elementsConfiguration, $databaseData);

            $this->saveToDatabase($databaseData, $table, $iterationCount);
        }
    }

    /**
     * This action will do mostly the same processing as the default processing but we need to set prefix for the finisher to find the correct element.
     *
     * @param string               $containerPath         the identifier of the container to process, can be for example `RootContainer` or `RootContainer.0.NestedContainer`
     * @param array<string, mixed> $elementsConfiguration finisher-element-configuration
     * @param array<string, mixed> $databaseData          prepared data
     * @param string               $table                 Tablename to save data to
     * @param int                  $iterationCount        finisher iteration
     */
    protected function processContainer(
        string $containerPath,
        array $elementsConfiguration,
        array $databaseData,
        string $table,
        int $iterationCount,
    ): void {
        $containerValues = ArrayUtility::getValueByPath($this->getFormValues(), $containerPath, '.');
        foreach ($containerValues as $copyId => $containerItem) {
            $prefix = $containerPath.'.'.$copyId.'.';
            // store data inside new array to keep prepared $databaseData for all iterations
            $itemDatabaseData = $this->prepareData($elementsConfiguration, $databaseData, $containerItem, $prefix);

            $this->saveToDatabase($itemDatabaseData, $table, $iterationCount, $copyId);
        }
    }

    /**
     * Adapted method for container data.
     *
     * @param array<array-key, mixed> $databaseData prepared data
     * @param array<string, mixed>    $values       optional filled Array with form values to use
     * @param string                  $prefix       prefix to get the form element object by a full identifier
     *
     * @return array<string, mixed> the filled database data
     */
    protected function prepareData(// @phpstan-ignore missingType.iterableValue
        array $elementsConfiguration,
        array $databaseData,
        array $values = [],
        string $prefix = '',
    ): array {
        if ([] === $values) {
            $values = $this->getFormValues();
        }

        foreach ($values as $elementIdentifier => $elementValue) {
            if (!$this->canValueBeHandled($elementValue, $elementsConfiguration, $elementIdentifier, $prefix)) {
                continue;
            }

            $elementValue = $this->resolveElementValue($elementValue, $elementsConfiguration[$elementIdentifier] ?? []);
            $databaseData[$elementsConfiguration[$elementIdentifier]['mapOnDatabaseColumn']] = $elementValue;
        }

        return $databaseData;
    }

    /**
     * Save or insert the values from
     * $databaseData into the table $table
     * and provide some finisher variables.
     */
    protected function saveToDatabase(// @phpstan-ignore missingType.iterableValue
        array $databaseData,
        string $table,
        int $iterationCount,
        ?int $containerItemKey = null,
    ): void {
        if ([] === $databaseData) {
            return;
        }

        if ('update' === $this->parseOption('mode')) {
            $whereClause = $this->parseOption('whereClause');
            $whereClause = is_array($whereClause) ? $whereClause : [];
            /** @var array<string, mixed> $resolvedWhereClause */
            $resolvedWhereClause = [];
            foreach ($whereClause as $columnName => $columnValue) {
                $resolvedWhereClause[(string) $columnName] = $this->parseOption('whereClause.'.$columnName);
            }
            $this->databaseConnection->update(
                $table,
                $databaseData,
                $resolvedWhereClause,
            );
        } else {
            $this->databaseConnection->insert($table, $databaseData);
            $insertedUid = (int) $this->databaseConnection->lastInsertId();
            $this->finisherContext->getFinisherVariableProvider()->add(
                $this->shortFinisherIdentifier,
                'insertedUids.'.$iterationCount.(is_int($containerItemKey) ? '.'.$containerItemKey : ''),
                $insertedUid,
            );

            $currentCount = (int) $this->finisherContext->getFinisherVariableProvider()->get(
                $this->shortFinisherIdentifier,
                'countInserts.'.$iterationCount,
            );
            $this->finisherContext->getFinisherVariableProvider()->addOrUpdate(
                $this->shortFinisherIdentifier,
                'countInserts.'.$iterationCount,
                $currentCount + 1,
            );
        }
    }

    private function resolveElementValue(mixed $elementValue, mixed $elementConfig): mixed
    {
        if ($elementValue instanceof FileReference) {
            $saveFileIdentifierInsteadOfUid = (bool) ($elementConfig['saveFileIdentifierInsteadOfUid'] ?? false);

            return $saveFileIdentifierInsteadOfUid
                ? $elementValue->getOriginalResource()->getCombinedIdentifier()
                : $elementValue->getOriginalResource()->getProperty('uid_local');
        }

        if (is_array($elementValue)) {
            return implode(',', $elementValue);
        }

        if ($elementValue instanceof DateTimeInterface) {
            $format = $elementConfig['dateFormat'] ?? 'U';

            return $elementValue->format($format);
        }

        return $elementValue;
    }

    /**
     * This will check if a element shall or can be handled.
     *
     * @param array<string, mixed> $elementsConfiguration
     */
    private function canValueBeHandled(mixed $elementValue, array $elementsConfiguration, string $elementIdentifier, string $prefix): bool
    {
        if (!isset($elementsConfiguration[$elementIdentifier])) {
            return false;
        }
        $elementConfig = $elementsConfiguration[$elementIdentifier];

        if (
            (null === $elementValue || '' === $elementValue)
            && isset($elementConfig['skipIfValueIsEmpty'])
            && true === $elementConfig['skipIfValueIsEmpty']
        ) {
            return false;
        }

        $element = $this->getElementByIdentifier($prefix.$elementIdentifier);
        if (!($element instanceof FormElementInterface) || !isset($elementConfig['mapOnDatabaseColumn'])) {
            return false;
        }

        return true;
    }
}

<?php

namespace NovaExportConfiguration\Casts\Json;

use NovaExportConfiguration\Enums\ConfigValueType;
use JsonFieldCast\Json\AbstractMeta;

class ExportConfigFilters extends AbstractMeta
{

    const CONDITION_TYPE = 'condition_type';

    /* -------------------
     * Skippable Filter
     ------------------- */

    public function setSkippableFilter(string $key, $value)
    {
        if (!is_null($value) && $value != ConfigValueType::SKIP->value) {
            $this->setAttribute($key, $value);
        } else {
            $this->removeAttribute($key);
        }

        return $this;
    }

    public function getSkippableFilter(string $key)
    {
        return $this->getAttribute($key) ?? ConfigValueType::SKIP->value;
    }

    /* -------------------
     * Boolean
     ------------------- */

    public function setFilterBoolean(string $key, $value)
    {
        $this->setAttribute($key, (bool)$value);
    }

    public function getFilterBoolean(string $key)
    {
        return (bool)$this->getAttribute($key);
    }

    /* -------------------
    * Boolean Group
    ------------------- */

    public function setFilterBooleanGroup(string $key, array $value)
    {
        $this->setAttribute($key, array_filter($value));
    }

    public function getFilterBooleanGroup(string $key)
    {
        return $this->getFilterArrayItems($key);
    }

    /* ===================
    * Array
    =================== */

    public function setFilterArrayItem(
        string $attributeName,
        string $key,
               $value,
        callable $condition = null
    ): self {
        if (!$condition) {
            $condition = fn () => true;
        }

        $data = $this->getAttribute($attributeName);
        if (!is_array($data)) {
            $data = [];
        }

        if ($value && call_user_func($condition, $value, $key, $attributeName)) {
            $data[$key] = $value;
        } elseif (isset($data[$key])) {
            unset($data[$key]);
        }

        $this->setAttribute($attributeName, $data);

        return $this;
    }

    public function getFilterArrayItem(string $attributeName, string $key, $default = null)
    {
        $data = $this->getAttribute($attributeName);
        if (!is_array($data)) {
            $data = [];
        }

        if (isset($data[$key])) {
            return $data[$key];
        }

        return $default;
    }

    public function getFilterArrayItems(string $attributeName): array
    {
        $data = $this->getAttribute($attributeName);
        if (!is_array($data)) {
            $data = [];
        }

        return $data;
    }

    /* ===================
     * Helpers
     =================== */

    public function getQueryType(string $key, string $default = null, string $attributeName = null): string
    {
        if (is_null($default)) {
            $default = ConfigValueType::AND->value;
        }
        if (is_null($attributeName)) {
            $attributeName = static::CONDITION_TYPE;
        }

        $boolean = $this->getFilterArrayItem($attributeName, $key, $default);
        if (!in_array($boolean, [
            ConfigValueType::AND->value,
            ConfigValueType::OR->value,
        ])) {
            return $default;
        }

        return $boolean;
    }
}

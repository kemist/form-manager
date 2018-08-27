<?php
declare(strict_types = 1);

namespace FormManager\Inputs;

use FormManager\Node;

/**
 * Class representing a HTML textarea element
 */
class Select extends Input
{
    private $allowNewValues = false;
    private $options = [];

    public function __construct(array $options, array $attributes = [])
    {
        parent::__construct('select', $attributes);

        foreach ($options as $value => $label) {
            if (is_array($label)) {
                $this->addOptgroup($value, $label);
                continue;
            }

            $this->addOption($value, (string) $label);
        }
    }

    public function allowNewValues(bool $allowNewValues = true): self
    {
        $this->allowNewValues = $allowNewValues;

        return $this;
    }

    protected function setValue($value)
    {
        if ($this->allowNewValues) {
            $this->addNewValues((array) $value);
        }

        if ($this->getAttribute('multiple')) {
            return $this->setMultipleValues((array) $value);
        }

        $this->value = null;

        foreach ($this->options as $option) {
            if ((string) $option->value === (string) $value) {
                $this->value = $option->value;
                $option->selected = true;
            } else {
                $option->selected = false;
            }
        }
    }

    protected function setMultipleValues(array $values)
    {
        $this->value = [];

        $values = array_map(
            function ($value) {
                return (string) $value;
            },
            $values
        );

        foreach ($this->options as $option) {
            if (in_array((string) $option->value, $values, true)) {
                $option->selected = true;
                $this->value[] = $option->value;
            } else {
                $option->selected = false;
            }
        }
    }

    protected function addNewValues(array $values)
    {
        foreach ($values as $value) {
            foreach ($this->options as $option) {
                if ((string) $option->value === (string) $value) {
                    continue 2;
                }
            }

            $this->addOption($value);
        }
    }

    private function addOptgroup($label, array $options)
    {
        $optgroup = new Node('optgroup', compact('label'));

        foreach ($options as $value => $label) {
            $this->addOption($value, $label, $optgroup);
        }

        $this->appendChild($optgroup);
    }

    private function addOption($value, string $label = null, Node $parent = null)
    {
        $option = new Node('option', compact('value'));
        $option->innerHTML = $label ?: (string) $value;

        $this->options[] = $option;

        if ($parent) {
            $parent->appendChild($option);
        } else {
            $this->appendChild($option);
        }
    }
}
<?php
namespace FormManager\Fields;

use FormManager\CollectionInterface;

class Duplicable extends Collection implements CollectionInterface {
	public $field;

	protected $index = 0;
	protected $parentPath;

	public function __construct ($children = null) {
		if (is_array($children)) {
			$this->field = new Collection($children);
		} else {
			$this->field = clone $children;
		}
	}

	public function add ($key, $value = null) {
		$this->field->add($key, $value);

		return $this;
	}

	public function load ($value = null, $file = null) {
		if (($sanitizer = $this->sanitizer) !== null) {
			$value = $sanitizer($value);
		}

		foreach ($value as $key => $value) {
			$child = isset($this->children[$key]) ? $this->children[$key] : $this->appendChild();

			$child->load($value, isset($file[$key]) ? $file[$key] : null);
		}

		return $this;
	}

	public function val ($value = null) {
		if ($value === null) {
			return parent::val();
		}

		foreach ($value as $key => $value) {
			$child = isset($this->children[$key]) ? $this->children[$key] : $this->appendChild();
			
			$child->val($value);
		}

		return $this;
	}

	protected function appendChild ($index = null) {
		$child = clone $this->field;

		if ($index === null) {
			$index = ++$this->index;
		}

		$this->children[$index] = $child->setParent($this);
		$this->prepareChild($child, $index, $this->parentPath);

		return $child;
	}

	public function addDuplicate ($index = null) {
		$this->appendChild($index);

		return $this;
	}

	public function prepareChildren ($parentPath) {
		$this->parentPath = $parentPath;

		foreach ($this->children as $key => $child) {
			$this->prepareChild($child, $key, $this->parentPath);
		}
	}
}

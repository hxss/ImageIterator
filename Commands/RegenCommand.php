<?php

namespace Modules\ImageIterator\Commands;

use Phact\Commands\Command;
use Phact\Main\Phact;
use Phact\Orm\Fields\ImageField;
use Phact\Storage\Files\FileInterface;

class RegenCommand extends Command
{
	public $modelsFolder = 'Models';

	public $filters = [];

	public $silent = false;

	public function handle($arguments = []) {
//		Main\Car[5]::image
		if (isset($arguments[0]))
			$this->initFilters($arguments[0]);

		$imgFields = $this->getImgFields();
		$imgFields = $this->filterFields($imgFields);

		foreach ($imgFields as $model => $fields) {
			echo $model;
			echo PHP_EOL;

			foreach ($fields as $fieldName) {
				$manager = $model::objects();
				if (isset($this->filters['id'])) {
					$manager = $manager->filter(['pk' => $this->filters['id']]);
				}
				$objects = $manager->group([$fieldName])->all();

				foreach ($objects as $k => $object) {
					echo "\t{$fieldName} [" . ($k + 1) . "\\" . count($objects) . "]\r";

					$field = $object->getField($fieldName);
					if (is_a($field->getOldAttribute(), FileInterface::class)) {

						foreach (array_keys($field->sizes) as $prefix) {
							$field->getStorage()->delete($field->sizeStoragePath($prefix, $field->getOldAttribute()));
						}
					}

					$field->createSizes();
				}
				echo "\t{$fieldName} [" . count($objects) . "\\" . count($objects) . "]";
				echo ' ';
				echo $this->color('Done!', 'green');
				echo PHP_EOL;
			}
		}
	}

	public function run($arguments = []) {
		$this->handle($arguments);
	}

	public function filterFields($imgFields) {
		$filters = $this->filters;

		array_walk($imgFields, function(&$v, $k) use($filters) {
			if (
				(isset($filters['module']) && $k::getModuleName() !== $filters['module'])
				|| (isset($filters['class']) && $k::classNameShort() !== $filters['class'])
				|| (isset($filters['field']) && !in_array($filters['field'], $v))
			)
				$v = false;
			else
				$v = isset($filters['field']) ? [$filters['field']] : $v;
		});
		return array_filter($imgFields);
	}

	public function initFilters($arguments) {
		preg_match(
			"/((?P<module>\w+)\\\\)?(?P<class>\w+)(\[(?P<id>\d+)\])?(::(?P<field>\w+))?/",
			$arguments,
			$matches
		);

		return $this->filters = array_filter($matches, function($v, $k) {
			return is_string($k) && $v;
		}, ARRAY_FILTER_USE_BOTH);
	}

	public function getImgFields() {
		$models = $this->getModels();
		foreach ($models as $model) {
			$imgFields[$model] = array_keys(array_filter($model::getFields(), function($field) {
				return is_a($field['class'], ImageField::class, true);
			}));
		}

		return array_filter($imgFields);
	}

	public function getModels() {
		$modules = Phact::app()->getModulesList();
		$models = [];
		foreach ($modules as $moduleName) {
			$module = Phact::app()->getModule($moduleName);
			$modulePath = $module::getPath();
			$path = implode(DIRECTORY_SEPARATOR, [$modulePath, $this->modelsFolder]);
			if (is_dir($path)) {
				foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $filename)
				{
					if ($filename->isDir() || $filename->getExtension() != 'php') continue;
					$name = $filename->getBasename('.php');
					$models[] = implode('\\', ['Modules', $module::getName(), $this->modelsFolder, $name]);
				}
			}
		}

		return $models;
	}
}

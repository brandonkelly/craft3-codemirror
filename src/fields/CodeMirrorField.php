<?php
/**
 * CodeMirror plugin for Craft CMS 3.x
 *
 * Add the awesome in-browser code editor CodeMirror as a field type.
 *
 * @link      https://wesleyluyten.com
 * @copyright Copyright (c) 2017 Wesley Luyten
 */

namespace luwes\codemirror\fields;

use luwes\codemirror\CodeMirror;
use luwes\codemirror\CodeMirrorAsset;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Db;
use yii\db\Schema;
use craft\helpers\Json;

/**
 * @author    Wesley Luyten
 * @package   CodeMirror
 * @since     1.0.0
 */
class CodeMirrorField extends Field
{
	// Public Properties
	// =========================================================================

	/**
	 * @var string
	 */
	//public $someAttribute;

	// Static Methods
	// =========================================================================

	/**
	 * @inheritdoc
	 */
	public static function displayName(): string
	{
		return Craft::t('codemirror', 'CodeMirror');
	}

	// Public Methods
	// =========================================================================

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		$rules = parent::rules();
		$rules = array_merge($rules, [
			// ['someAttribute', 'string'],
		]);
		return $rules;
	}

	/**
	 * @inheritdoc
	 */
	public function getContentColumnType(): string
	{
		return Schema::TYPE_TEXT;
	}

	/**
	 * @inheritdoc
	 */
	public function normalizeValue($value, ElementInterface $element = null)
	{
		return $value;
	}

	/**
	 * @inheritdoc
	 */
	public function serializeValue($value, ElementInterface $element = null)
	{
		return parent::serializeValue($value, $element);
	}

	/**
	 * @inheritdoc
	 */
	public function getSettingsHtml()
	{
		// Render the settings template
		return Craft::$app->getView()->renderTemplate(
			'codemirror'
			. DIRECTORY_SEPARATOR
			. '_components'
			. DIRECTORY_SEPARATOR
			. 'fields'
			. DIRECTORY_SEPARATOR
			. '_settings',
			[
				'field' => $this,
			]
		);
	}

	/**
	 * @inheritdoc
	 */
	public function getInputHtml($value, ElementInterface $element = null): string
	{
		$am = Craft::$app->getAssetManager();
		$view = Craft::$app->getView();

		// Register our asset bundle
		$view->registerAssetBundle(CodeMirrorAsset::class);

		// Get our id and namespace
		$id = $view->formatInputId($this->handle);
		$namespacedId = $view->namespaceInputId($id);

		// Variables to pass down to our field JavaScript to let it namespace properly
		$options = Craft::$app->config->get('jsOptions', 'codemirror');

		if (!empty($options['theme']) && $options['theme'] != 'default')
		{
			$theme = $options['theme'];
			$view->registerCssFile($am->getPublishedUrl('@luwes/codemirror/assets', true)."/theme/{$theme}.css", [
                'depends' => CodeMirrorAsset::class
            ]);
		}

		$addons = Craft::$app->config->get('addons', 'codemirror');
		if (!empty($addons))
		{
			foreach ($addons as $addon)
			{
				$view->registerJsFile($am->getPublishedUrl('@luwes/codemirror/assets', true)."/addon/{$addon}.js", [ 'depends' => CodeMirrorAsset::class ]);
			}
		}

		$modes = Craft::$app->config->get('modes', 'codemirror');
		if (!empty($modes))
		{
			foreach ($modes as $mode)
			{
				$view->registerJsFile($am->getPublishedUrl('@luwes/codemirror/assets', true)."/mode/{$mode}/{$mode}.js", [ 'depends' => CodeMirrorAsset::class ]);
			}
		}

		$options = Json::encode($options);
		$view->registerJs("CodeMirror.fromTextArea($('#$namespacedId')[0], $options);");

		// Render the input template
		return $view->renderTemplate(
			'codemirror'
			. DIRECTORY_SEPARATOR
			. '_components'
			. DIRECTORY_SEPARATOR
			. 'fields'
			. DIRECTORY_SEPARATOR
			. '_input',
			[
				'name' => $this->handle,
				'value' => $value,
				'field' => $this,
				'id' => $id,
				'namespacedId' => $namespacedId,
			]
		);
	}
}

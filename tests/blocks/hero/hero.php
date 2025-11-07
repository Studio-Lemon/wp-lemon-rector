<?php

/**
 * ACF Block declaration
 *
 * @package WordPress
 * @subpackage WP_Lemon
 */

namespace WP_Lemon\Child\Blocks;

use HighGround\Bulldozer\BlockRendererV2 as BlockRenderer;

/**
 * Example block that can be copied for making extra blocks.
 *
 * Follow the API standard of https://www.advancedcustomfields.com/resources/acf-blocks-with-block-json/
 */
class Hero_Block extends BlockRenderer
{

	/**
	 * The name of the block.
	 * This needs to be the same as the folder and file name.
	 */
	const NAME = 'hero';

	/**
	 * Extend the base context of our block.
	 * With this function we can add for hero a query or
	 * other custom content.
	 *
	 * @param array $context      Holds the block data.
	 * @return array  $context    Returns the array with the extra content that merges into the original block context.
	 */
	public function block_context($context): array
	{
		$this->classes[] = 'section hero alignfull has-background';
		$this->attributes['align'] = 'full';
		$this->attributes['align'] = 'wide';

		if (isset($this->fields['image_field']) && 12 == $this->fields['image_field']) {
			// Do something
			$image = $this->fields['image_field'];
		}

		$this->attributes['id'] = 'random';

		$this->block_disabled = true;

		if ($this->is_preview) {
			$this->add_notification('This is a preview of the card slider block. The slider will not work in the editor.', 'notice');
		}

		$args = [
			'fluid' => true,
			'InnerBlocks' => '<InnerBlocks allowedBlocks="' . esc_attr(wp_json_encode($allowed_blocks)) . '" template="' . esc_attr(wp_json_encode($template)) . '" />',
		];

		return array_merge($context, $args);
	}


	/**
	 * Register fields to the block.
	 *
	 * @link https://github.com/StoutLogic/acf-builder
	 * @return StoutLogic\AcfBuilder\FieldsBuilder
	 */
	public function add_fields(): object
	{
		$this->registered_fields
			->addText('title', [
				'label' => 'Title',
				'required' => 1,
			])
			->addText('subtitle', [
				'label' => 'Subtitle',
				'reguired' => 1,
			])
			->addImage('image_field', [
				'label' => 'Image Field',
				'required' => 1,
				'return_format' => 'id',
			])
			->addLink('link_1', [
				'label' => 'Button one',
				'required' => 0,
			])
			->addLink('link_2', [
				'label' => 'Button two',
				'required' => 0,
			]);
		return $this->registered_fields;
	}
}

/**
 * Enable the class
 */
new Hero_Block();

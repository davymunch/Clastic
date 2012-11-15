<?php
/**
 * This file is part of the Clastic package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Clastic\Block;

/**
 * The blockCollection that stores all blocks.
 */
class BlockCollection
{
	/**
	 * Holds a list of all the blocks.
	 *
	 * @var Block[]
	 */
	protected $blocks = array();

	public function addBlock(Block &$block, $name = null)
	{
		if (is_null($name)) {
			$name = $block->getName();
		}
		$this->blocks[$name] = $block;
	}

	/**
	 * Render all blocks and return them in a structured array [region][name]
	 *
	 * @return string[]
	 */
	public function renderBlocks()
	{
		$structuredBlocks = array();
		foreach ($this->blocks as $block) {
			$structuredBlocks[$block->getRegion()][$block->getName()] = $block;
		}
		$this->sortBlocks($structuredBlocks);
		$renderedBlocks = array();
		foreach ($structuredBlocks as $region) {
			foreach ($region as $block) {
				$renderedBlocks[$block->getRegion()][$block->getName()] = $block->getContent();
			}
		}
		return $renderedBlocks;
	}

	/**
	 * Sort all the blocks per region.
	 *
	 * @param $blocks
	 */
	protected function sortBlocks(&$blocks) {
		foreach ($blocks as &$region)
		{
			usort($region, function($block1, $block2) {
				return ($block1->getWeight() < $block2->getWeight()) ? -1 : 1;
			});
		}
	}
}

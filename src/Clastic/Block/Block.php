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

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Block holds parts of a webpage.
 */
class Block
{
	/**
	 * Hold all parameters in a parameterBag.
	 *
	 * @var \Symfony\Component\HttpFoundation\ParameterBag
	 */
	protected $parameters;

	/**
	 * The name of the block.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The region where the block is rendered in.
	 *
	 * @var string
	 */
	protected $region;

	/**
	 * The callback that returns the content of the block.
	 *
	 * @var
	 */
	protected $callback;

	/**
	 * The content of the block.
	 *
	 * @var
	 */
	protected $content;

	/**
	 * The weight of the current block.
	 * Blocks are sorted by the weight grouped in region-groups.
	 *
	 * @var int
	 */
	protected $weight = 0;

	/**
	 * Constructor of the block.
	 *
	 * @param $name
	 */
	public function __construct($name)
	{
		$this->parameters = new ParameterBag();
		$this->name = $name;
	}

	/**
	 * Getter for the block's name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Setter for the block's name.
	 *
	 * @param $name string
	 * @return Block
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * Getter for the block's region.
	 *
	 * @return string
	 */
	public function getRegion()
	{
		if (is_null($this->region)) {
			return 'content';
		}
		return $this->region;
	}

	/**
	 * Setter for the block's region.
	 *
	 * @param $region string
	 * @return Block
	 */
	public function setRegion($region)
	{
		$this->region = $region;
		return $this;
	}

	/**
	 * Setter for the block's callback.
	 *
	 * @param $callback callback
	 * @return Block
	 * @throws \Exception
	 */
	public function setCallback($callback)
	{
		if (!is_callable($callback)) {
			throw new \Exception('Callback needs to be a valid callback.');
		}
		$this->callback = $callback;
		return $this;
	}

	/**
	 * Getter for the block's content.
	 *
	 * @return string
	 */
	public function getContent()
	{
		if (is_null($this->content)) {
			if (!is_null($this->callback)) {
				$this->content = call_user_func($this->callback);
			}
		}
		return $this->content;
	}

	/**
	 * Setter for the block's content.
	 *
	 * @param $content string
	 * @return Block
	 */
	public function setContent($content)
	{
		$this->content = $content;
		return $this;
	}

	/**
	 * Getter for the block's weight.
	 *
	 * @return int
	 */
	public function getWeight()
	{
		return $this->weight;
	}

	/**
	 * Setter for the block's weight.
	 *
	 * @param $weight int
	 * @return Block
	 */
	public function setWeight($weight)
	{
		$this->weight = $weight;
		return $this;
	}

}

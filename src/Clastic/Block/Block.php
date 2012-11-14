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

class Block
{
	protected $parameters;

	protected $name;

	protected $region;

	protected $callback;

	protected $content;

	protected $weight = 0;

	public function __construct($name)
	{
		$this->parameters = new ParameterBag();
		$this->name = $name;
	}

	public function getName()
	{
		return $this->name;
	}

	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	public function getRegion()
	{
		if (is_null($this->region)) {
			return 'content';
		}
		return $this->region;
	}

	public function setRegion($region)
	{
		$this->region = $region;
		return $this;
	}

	public function setCallback($callback)
	{
		if (!is_callable($callback)) {
			throw new \Exception('Callback needs to be a valid callback.');
		}
		$this->callback;
	}

	public function getContent()
	{
		if (is_null($this->content)) {
			if (!is_null($this->callback)) {
				$this->content = call_user_func($this->callback);
			}
		}
		return $this->content;
	}

	public function setContent($content)
	{
		$this->content = $content;
		return $this;
	}

	public function getWeight()
	{
		return $this->weight;
	}

	public function setWeight($weight)
	{
		$this->weight = $weight;
		return $this;
	}

}
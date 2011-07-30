<?php

class DomCreator
{

	const ATTRIBUTE_SIGN = '_';

	private $_doc;
	
	private $_node;
	
	private $_nsUri;
	
	private $_prefix;
	
	private $_qualifyAttributes;
	
	private $_forceNewElement;
	
	/**
	 * Start creating a new DOMDocument with the specified namespace URI,
	 * namespace prefix, root element, and whether attributes will be
	 * qualified.
	 */
	public static function create($nsUri, $nsPrefix, $root,
			$qualifyAttributes = false)
	{
		$doc = new DOMDocument();
		$prefix = $nsPrefix === null ? '' : $nsPrefix . ':';
		$xml = new self($doc, $doc, $nsUri, $prefix, $qualifyAttributes);
		return $xml->$root;
	}

	/**
	 * Start creating a new DOMDocument without a namespace, with the specified
	 * root element.
	 */
	public static function createNoNamespace($root)
	{
		return self::create(null, null, $root);
	}

	/**
	 * Start creating a new DOMDocument -- to be used as a fragment -- with the
	 * specified namespace URI, namespace prefix, and whether attributes will
	 * be qualified.
	 */
	public static function createFragment($nsUri, $nsPrefix,
			$qualifyAttributes = false)
	{
		return self::create($nsUri, $nsPrefix, 'fragment', $qualifyAttributes);
	}

	/**
	 * Start creating a new DOMDocument -- to be used as a fragment.
	 */
	public static function createFragmentNoNamespace()
	{
		return self::createFragment(null, null);
	}

	private function __construct($doc, $node, $nsUri, $prefix,
			$qualifyAttributes, $forceNewElement = false)
	{
		$this->_doc = $doc;
		$this->_node = $node;
		$this->_nsUri = $nsUri;
		$this->_prefix = $prefix;
		$this->_qualifyAttributes = $qualifyAttributes;
		$this->_forceNewElement = $forceNewElement;
	}

	/**
	 * Get the DOMDocument we're in the process of creating.
	 */
	public function getDocument()
	{
		return $this->_doc;
	}

	/**
	 * Get the underlying DOMElement of this DomCreator node.
	 */
	public function getNode()
	{
		return $this->_node;
	}

	/**
	 * Creates a new element to contain others, or returns the last one if it
	 * has the same name.
	 */
	public function __get($name)
	{
		if ($this->_node->lastChild !== null &&
				$this->_node->lastChild->nodeName === $this->_prefix . $name &&
				!$this->_forceNewElement)
		{
			$element = $this->_node->lastChild;
		}
		else
		{
			$element = $this->_element($name);
		}
		return new self($this->_doc, $element, $this->_nsUri, $this->_prefix,
				$this->_qualifyAttributes);
	}

	/**
	 * Creates a new element or attribute with some content.
	 */
	public function __set($name, $value)
	{
		if (strpos($name, self::ATTRIBUTE_SIGN) === 0)
		{
			$attributeName = substr($name, strlen(self::ATTRIBUTE_SIGN));
			if ($this->_qualifyAttributes)
			{
				$attribute = $this->_doc->createAttributeNS($this->_nsUri,
						$this->_prefix . $attributeName);
			}
			else
			{
				$attribute = $this->_doc->createAttribute($attributeName);
			}
			$attribute->value = $value;
			$this->_node->appendChild($attribute);
		}
		else
		{
			$element = $this->_element($name, $value);
			if ($value instanceof self)
			{
				$this->_import($element, $value->_node->childNodes);
			}
			else if ($value instanceof DOMNode)
			{
				$this->_import($element, $value->childNodes);
			}
			else
			{
				$text = $this->_doc->createTextNode($value);
				$element->appendChild($text);
			}
		}
	}
	
	public function forceNewElement()
	{
		return new self($this->_doc, $this->_node, $this->_nsUri,
				$this->_prefix, $this->_qualifyAttributes, true);
	}
	
	private function _import($element, $nodes)
	{
		foreach ($nodes as $node)
		{
			$imported = $this->_doc->importNode($node, true);
			$element->appendChild($imported);
		}
	}
	
	private function _element($name)
	{
		$element = $this->_doc->createElementNS($this->_nsUri,
				$this->_prefix . $name);
		$this->_node->appendChild($element);
		return $element;
	}
	
	public function __toString()
	{
		return '<' . $this->_node->nodeName . '>..';
	}
}


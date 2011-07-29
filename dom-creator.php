<?php

class DomCreator
{

	const ATTRIBUTE_SIGN = '_';

	private $_doc;
	
	private $_node;
	
	private $_nsUri;
	
	private $_prefix;
	
	/**
	 * Start creating a new DOMDocument with the specified namespace URI, namespace prefix, and root element.
	 */
	public static function create($nsUri = null, $nsPrefix = null, $root = 'root')
	{
		$doc = new DOMDocument();
		$prefix = empty($nsPrefix) ? '' : $nsPrefix . ':';
		$xml = new self($doc, $doc, $nsUri, $prefix);
		return $xml->$root;
	}

	private function __construct($doc, $node, $nsUri, $prefix)
	{
		$this->_doc = $doc;
		$this->_node = $node;
		$this->_nsUri = $nsUri;
		$this->_prefix = $prefix;
	}

	/**
	 * Get the DOMDocument we're in the process of creating.
	 */
	public function getDocument()
	{
		return $this->_doc;
	}

	/**
	 * Get the underlying DOMElement of the DomCreator node.
	 */
	public function getNode()
	{
		return $this->_node;
	}

	/**
	 * Creates a new element to contain others, or returns the last one if it has the same name.
	 */
	public function __get($name)
	{
		if ($this->_node->lastChild !== null && $this->_node->lastChild->nodeName === $this->_prefix . $name)
		{
			$element = $this->_node->lastChild;
		}
		else
		{
			$element = $this->_element($name);
		}
		return new self($this->_doc, $element, $this->_nsUri, $this->_prefix);
	}

	/**
	 * Creates a new element or attribute with some content.
	 */
	public function __set($name, $value)
	{
		if (strpos($name, self::ATTRIBUTE_SIGN) === 0)
		{
			$attributeName = substr($name, strlen(self::ATTRIBUTE_SIGN));
			$attribute = $this->_doc->createAttributeNS($this->_nsUri, $this->_prefix . $attributeName);
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
		$element = $this->_doc->createElementNS($this->_nsUri, $this->_prefix . $name);
		$this->_node->appendChild($element);
		return $element;
	}
	
	public function __toString()
	{
		return '<' . $this->_node->nodeName . '>..';
	}
}


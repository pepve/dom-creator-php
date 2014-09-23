<?php

/**
 * # DOM Creator for PHP
 *
 * A limited but simple API for creating DOMDocumentS in PHP.
 *
 * - Built with minimum verbosity in mind
 * - Works very well for straightforward schemas
 *
 * Short example:
 *
 * ```php
 * <?
 * $foo = DomCreator::create('http://example.com/foo', 'f', 'foo');
 * $foo->ident->name = 'John Doe';
 * $foo->ident->number = '123';
 * $foo->content = 'Hello, World!';
 * ```
 *
 * Generates:
 *
 * ```xml
 * <?xml version="1.0"?>
 * <f:foo xmlns:f="http://example.com/foo">
 *   <f:ident>
 *     <f:name>John Doe</f:name>
 *     <f:number>123</f:number>
 *   </f:ident>
 *   <f:content>Hello, World!</f:content>
 * </f:foo>
 * ```
 *
 * You first create a `DomCreator` instance with one of the factory functions.
 * Then get and set properties on the object to create a DOM. New complex
 * elements (the ones that contain other elements) are automatically created
 * when you try to read them/set a property on them. This might look a little
 * like setting properties on a regular object. The difference is that each
 * action appends an element to the tree, instead of overwriting it. So the
 * sequence of actions matters, contrary to setting stuff on an object.
 *
 * When you try to get a property with the same name as the one you got
 * previously, the previous node is returned. This is not always wanted, so this
 * behaviour can be avoided with `closeChild()`.
 *
 * To write an attribute, prefix the property name with an underscore (this is
 * set by `DomCreator::ATTRIBUTE_SIGN`). Elements and attributes that contain
 * characters not allowed in a PHP identifier can be created by calling
 * `__get(..)` and `__set(..)` yourself. Although you should think of using
 * another API if this happens a lot.
 *
 * License: MIT
 */

class DomCreator
{

	const ATTRIBUTE_SIGN = '_';

	private $_doc;
	
	private $_node;
	
	private $_nsUri;
	
	private $_prefix;
	
	private $_qualifyAttributes;
	
	private $_lastChild;
	
	/**
	 * Create a new instance with a namespace and the given root element. The
	 * specified namespace URI and namespace prefix are used for all elements,
	 * but by default not for attributes. This can be changed by using true as
	 * the fourth argument.
	 */
	public static function create($nsUri, $nsPrefix, $root,
			$qualifyAttributes = false)
	{
		$doc = new DOMDocument();
		$prefix = $nsPrefix === null ? '' : $nsPrefix . ':';
		$instance = new self($doc, $doc, $nsUri, $prefix, $qualifyAttributes);
		return $instance->$root;
	}

	/**
	 * Create a new instance with the given root element, and without a
	 * namespace. You can optionally set a default namespace yourself with
	 * `$domCreator->_xmlns = 'http://example.com/foo'`.
	 */
	public static function createNoNamespace($root)
	{
		return self::create(null, null, $root);
	}

	/**
	 * Create a new instance for use as a fragment. This allows functions to
	 * create some part of the DOM on their own. It can later be added to
	 * another tree like so: `$domCreator->FragmentContents = $fragment`. That
	 * action discards the fragment's root element, so this function allows you
	 * not to care about it.
	 */
	public static function createFragment($nsUri, $nsPrefix,
			$qualifyAttributes = false)
	{
		return self::create($nsUri, $nsPrefix, 'fragment', $qualifyAttributes);
	}

	/**
	 * Create a new instance for use as a fragment, without a namespace.
	 */
	public static function createFragmentNoNamespace()
	{
		return self::createFragment(null, null);
	}

	private function __construct(DOMDocument $doc, DOMNode $node, $nsUri,
			$prefix, $qualifyAttributes)
	{
		$this->_doc = $doc;
		$this->_node = $node;
		$this->_nsUri = $nsUri;
		$this->_prefix = $prefix;
		$this->_qualifyAttributes = $qualifyAttributes;
	}

	/**
	 * Get a property. This creates and appends a new element with the name of
	 * the property and returns a new instance of this class to represent it.
	 * One exception: if the requested property has the same name as the last
	 * one requested and `closeChild()` has not been called, then return the
	 * same instance as on the last call (thus not creating a new element).
	 */
	public function __get($name)
	{
		if ($this->_lastChild !== null &&
				$this->_lastChild->_node->nodeName === $this->_prefix . $name)
		{
			return $this->_lastChild;
		}
		else
		{
			$element = $this->_element($name);
			$this->_lastChild = new self($this->_doc, $element,
					$this->_nsUri, $this->_prefix, $this->_qualifyAttributes);
			return $this->_lastChild;
		}
	}

	/**
	 * Set a property. If the property name starts with an underscore
	 * (`DomCreator::ATTRIBUTE_SIGN`) this creates and appends a new attribute
	 * with the given name (attribute sign stripped) and value. Else a new
	 * element is created with the value as its contents. The value can be
	 * another instance of this class or a DOMNode, in which case all of their
	 * children are inserted in the new element.
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
			$this->_lastChild = null;
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
	
	/**
	 * Close the last child so nothing more will be appended to it.
	 */
	public function closeChild()
	{
		$this->_lastChild = null;
	}
	
	/**
	 * Get the underlying DOMDocument of the tree, this is the same for each
	 * node in the tree.
	 */
	public function getDocument()
	{
		return $this->_doc;
	}

	/**
	 * Get the underlying DOMElement of this node.
	 */
	public function getNode()
	{
		return $this->_node;
	}

	// import into $element everything from $nodes
	private function _import(DOMElement $element, DOMNodeList $nodes)
	{
		foreach ($nodes as $node)
		{
			$imported = $this->_doc->importNode($node, true);
			$element->appendChild($imported);
		}
	}
	
	// create and append an element with $name
	private function _element($name)
	{
		$element = $this->_doc->createElementNS($this->_nsUri,
				$this->_prefix . $name);
		$this->_node->appendChild($element);
		return $element;
	}
	
	// yay!
	public function __toString()
	{
		return '<' . $this->_node->nodeName . '>..';
	}
}


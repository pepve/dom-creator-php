<?php

class DomCreator
{

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
	 * Creates a new element with text content.
	 */
    public function __set($name, $value)
    {
        $this->_element($name, $value);
    }
    
    private function _element($name, $value = null)
    {
        $element = $this->_doc->createElementNS($this->_nsUri, $this->_prefix . $name);
        $this->_node->appendChild($element);
        if ($value instanceof self)
        {
            foreach ($value->_node->childNodes as $child)
            {
                $importedChild = $this->_doc->importNode($child, true);
                $element->appendChild($importedChild);
            }
        }
        else if ($value !== null)
        {
            $text = $this->_doc->createTextNode($value);
            $element->appendChild($text);
        }
        return $element;
    }
    
    public function __toString()
    {
        return '<' . $this->_node->nodeName . '>..';
    }
}


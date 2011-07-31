# DOM Creator for PHP

A limited but simple API for creating DOMDocumentS in PHP.

- Built with minimum verbosity in mind
- Works very well for straightforward schemas

Short example:

```php
<?
$foo = DomCreator::create('http://example.com/foo', 'f', 'foo');
$foo->ident->name = 'John Doe';
$foo->ident->number = '123';
$foo->content = 'Hello, World!';
```

Generates:

```xml
<?xml version="1.0"?>
<f:foo xmlns:f="http://example.com/foo">
  <f:ident>
    <f:name>John Doe</f:name>
    <f:number>123</f:number>
  </f:ident>
  <f:content>Hello, World!</f:content>
</f:foo>
```

You first create a `DomCreator` instance with one of the factory functions. Then get and set properties on the object to create a DOM. New complex elements (the ones that contain other elements) are automatically created when you try to read them/set a property on them. This might look a little like setting properties on a regular object. The difference is that each action appends an element to the tree, instead of overwriting it. So the sequence of actions matters, contrary to setting stuff on an object.

When you try to get a property with the same name as the one you got previously, the previous node is returned. This is not always wanted, so this behaviour can be avoided with `closeChild()`.

To write an attribute, prefix the property name with an underscore (this is set by `DomCreator::ATTRIBUTE_SIGN`). Elements and attributes that contain characters not allowed in a PHP identifier can be created by calling `__get(..)` and `__set(..)` yourself. Although you should think of using
another API if this happens a lot.

Originally at: https://github.com/pepve/dom-creator-php

## The API

#### DomCreator::create($nsUri, $nsPrefix, $root, $qualifyAttributes = false)

Create a new instance with a namespace and the given root element. The specified namespace URI and namespace prefix are used for all elements, but by default not for attributes. This can be changed by using true as the fourth argument.

#### DomCreator::createNoNamespace($root)

Create a new instance with the given root element, and without a namespace. You can optionally set a default namespace yourself with `$domCreator->_xmlns = 'http://example.com/foo'`.

#### DomCreator::createFragment($nsUri, $nsPrefix, $qualifyAttributes = false)

Create a new instance for use as a fragment. This allows functions to create some part of the DOM on their own. It can later be added to another tree like so: `$domCreator->FragmentContents = $fragment`. That action discards the fragment's root element, so this function allows you not to care about it.

#### DomCreator::createFragmentNoNamespace()

Create a new instance for use as a fragment, without a namespace.

####  __get($name)

Get a property. This creates and appends a new element with the name of the property and returns a new instance of this class to represent it. One exception: if the requested property has the same name as the last one requested and `closeChild()` has not been called, then return the same instance as on the last call (thus not creating a new element).

####  __set($name, $value)

Set a property. If the property name starts with an underscore (`DomCreator::ATTRIBUTE_SIGN`) this creates and appends a new attribute with the given name (attribute sign stripped) and value. Else a new element is created with the value as its contents. The value can be another instance of this class or a DOMNode, in which case all of their children are inserted in the new element.

####  closeChild()

Close the last child so nothing more will be appended to it.

####  getDocument()

Get the underlying DOMDocument of the tree, this is the same for each node in the tree.

####  getNode()

Get the underlying DOMElement of this node.

## Some Examples

### Example 1

```php
<?
$foo = DomCreator::create('http://example.com/foo', 'f', 'foo');

$foo->ident->_type = 'person';
$foo->ident->name = 'My Name';
$foo->ident->number = '1234';
$foo->content = 'Here is the content..';

$fooDom = $foo->getDocument();
$fooDom->formatOutput = true;
echo $fooDom->saveXml();
```

Output:

```xml
<?xml version="1.0"?>
<f:foo xmlns:f="http://example.com/foo">
  <f:ident type="person">
    <f:name>My Name</f:name>
    <f:number>1234</f:number>
  </f:ident>
  <f:content>Here is the content..</f:content>
</f:foo>
```

### Example 2

```php
<?
$bar = DomCreator::createNoNamespace('Bar');

$bar->One = 1;
$bar->Two = 2;
$bar->Three = 3;

$subBar = DomCreator::createFragmentNoNamespace();
foreach (array('A', 'B') as $i => $letter)
{
	$subBar->Letter->Sequence = $i;
	$subBar->Letter->Value = $letter;
	$subBar->closeChild();
}
$bar->Sub = $subBar;

$dom = new DOMDocument();
$dom->appendChild($dom->createElement('Test', 'value'));
$bar->DomPart = $dom;

$barDom = $bar->getDocument();
$barDom->formatOutput = true;
echo $barDom->saveXml();
```

Output:

```xml
<?xml version="1.0"?>
<Bar>
  <One>1</One>
  <Two>2</Two>
  <Three>3</Three>
  <Sub>
    <Letter>
      <Sequence>0</Sequence>
      <Value>A</Value>
    </Letter>
    <Letter>
      <Sequence>1</Sequence>
      <Value>B</Value>
    </Letter>
  </Sub>
  <DomPart>
    <Test>value</Test>
  </DomPart>
</Bar>
```

_Automatically generated by readme.php_

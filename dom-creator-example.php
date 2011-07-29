<?php

require 'dom-creator.php';

function printXml($domCreator)
{
	$document = $domCreator->getDocument();
	$document->formatOutput = true;
	echo $document->saveXml();
}

$foo = DomCreator::create('http://example.com/foo', 'f', 'foo');

$foo->ident->_type = 'person';
$foo->ident->name = 'My Name';
$foo->ident->number = '1234';
$foo->content = 'Here is the content..';

printXml($foo);

//<f:foo xmlns:f="http://example.com/foo">
//  <f:ident type="person">
//    <f:name>My Name</f:name>
//    <f:number>1234</f:number>
//  </f:ident>
//  <f:content>Here is the content..</f:content>
//</f:foo>


$bar = DomCreator::create(null, null, 'Bar');

$bar->One = 1;
$bar->Two = 2;
$bar->Three = 3;

$subBar = DomCreator::create();
foreach (array('A', 'B', 'C') as $letter)
{
	$subBar->$letter = "Letter $letter";
}
$bar->Sub = $subBar;

$dom = new DOMDocument();
$dom->appendChild($dom->createElement('Test', 'value'));
$bar->DomPart = $dom;

printXml($bar);

//<Bar>
//  <One>1</One>
//  <Two>2</Two>
//  <Three>3</Three>
//  <Sub>
//    <A>Letter A</A>
//    <B>Letter B</B>
//    <C>Letter C</C>
//  </Sub>
//  <DomPart>
//    <Test>value</Test>
//  </DomPart>
//</Bar>


$dom = new DOMDocument();
$test = $dom->createElement('test', 'value');
$dom->appendChild($test);
$attr = $dom->createAttribute('attr');
$attr->value = 1;
$test->appendChild($attr);

$super = DomCreator::create('http://test.com/test', 'tst', 'element');
$super->hello = 'world';
$super->hello->_to = 'you';
$super->dom = $dom;

printXml($super);

//<tst:element xmlns:tst="http://test.com/test">
//  <tst:hello tst:to="you">world</tst:hello>
//  <tst:dom>
//    <test attr="1">value</test>
//  </tst:dom>
//</tst:element>


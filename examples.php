<?php

require 'DomCreator.php';

function printXml($domCreator)
{
	$document = $domCreator->getDocument();
	$document->formatOutput = true;
	echo $document->saveXml();
}


###############################################################################
###############################################################################

$foo = DomCreator::create('http://example.com/foo', 'f', 'foo');

$foo->ident->_type = 'person';
$foo->ident->name = 'My Name';
$foo->ident->number = '1234';
$foo->content = 'Here is the content..';
$foo->show->that->nesting->is->easy;

printXml($foo);


###############################################################################
###############################################################################

$bar = DomCreator::createNoNamespace('Bar');

$bar->One = 1;
$bar->Two = 2;
$bar->Three = 3;

$subBar = DomCreator::createFragmentNoNamespace();
foreach (array('A', 'B', 'C') as $letter)
{
	$subBar->$letter = "Letter $letter";
}
$bar->Sub = $subBar;

$dom = new DOMDocument();
$dom->appendChild($dom->createElement('Test', 'value'));
$bar->DomPart = $dom;

printXml($bar);


###############################################################################
###############################################################################

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


###############################################################################
###############################################################################

$rep = DomCreator::createNoNamespace('Repetitive');

foreach (range(3, 17, 7) as $n)
{
	$rep->Numbers->forceNewElement()->Number->Value = $n;
	$rep->Numbers->Number->Even = $n % 2 === 0 ? 'Yes' : 'No';
	$rep->Numbers->Number->Hash = md5($n);
}

printXml($rep);



<?php

require 'dom-creator.php';

$foo = DomCreator::create('http://example.com/foo', 'f', 'foo');

$foo->ident->name = 'My Name';
$foo->ident->number = '1234';
$foo->content = 'Here is the content..';

$fooDom = $foo->getDocument();
$fooDom->formatOutput = true;
echo $fooDom->saveXml();

//<f:foo xmlns:f="http://example.com/foo">
//  <f:ident>
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

$barDom = $bar->getDocument();
$barDom->formatOutput = true;
echo $barDom->saveXml();

//<Bar>
//  <One>1</One>
//  <Two>2</Two>
//  <Three>3</Three>
//  <Sub>
//    <A>Letter A</A>
//    <B>Letter B</B>
//    <C>Letter C</C>
//  </Sub>
//</Bar>


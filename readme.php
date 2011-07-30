# DOM Creator for PHP

A limited but simple API for creating DOMDocumentS in PHP.


## Supports

- Elements that contain text
- Elements that contain other elements
- Attributes, optionally qualified
- Namespaces, as long as the whole fragment is of the same one
- Adding in (parts of) another DOMDocument
<?php

require 'DomCreator.php';

$examples['Example 1'] = <<<'EOT'
$foo = DomCreator::create('http://example.com/foo', 'f', 'foo');

$foo->ident->_type = 'person';
$foo->ident->name = 'My Name';
$foo->ident->number = '1234';
$foo->content = 'Here is the content..';

$fooDom = $foo->getDocument();
$fooDom->formatOutput = true;
echo $fooDom->saveXml();
EOT;

$examples['Example 2'] = <<<'EOT'
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

$barDom = $bar->getDocument();
$barDom->formatOutput = true;
echo $barDom->saveXml();
EOT;

foreach ($examples as $title => $code)
{
	echo "\n\n## $title\n\n";
	echo "```php\n<?\n$code\n```\n\n";
	echo "Output:\n\n";
	echo "```xml\n";
	eval($code);
	echo "```\n";
}


<?php

// This script generates README.md

// ############################################################
// So i really had to write my own phpdoc parser..

$file = file_get_contents('DomCreator.php');
preg_match_all("_\n[ \t]*/\\*\\*(.*?\\*)/([^{]*)_s", $file, $matches, PREG_SET_ORDER);

$doc = array();
foreach ($matches as $match)
{
	$comments = $match[1];
	$declaration = $match[2];
	
	$comments = preg_replace("_^([ \t]*\* ?)_m", '', $comments);
	$comments = preg_replace("_^([[:alnum:](].*)\n(?!$)_m", "\\1 ", $comments);
	$comments = preg_replace("_^\n_", '', $comments);
	
	if (strpos($declaration, 'class') !== false)
	{
		$doc['class'] = $comments;
		$doc['classname'] = trim(str_replace('class', '', $declaration));
	}
	else
	{
		$sign = preg_replace(array("/^\\s*([[:alnum:]]+\\s+)*/", "/\\s+/", "/\\s+$/"), array('', ' ', ''), $declaration);
		if (strpos($declaration, 'static') !== false)
		{
			$doc['static'][$sign] = $comments;
		}
		else
		{
			$doc['instance'][$sign] = $comments;
		}
	}
}


// ############################################################
// Print the docs

echo $doc['class'];

echo "\n## The API\n";

foreach ($doc['static'] as $sign => $comments)
{
	echo "\n#### $doc[classname]::$sign\n\n$comments";
}

foreach ($doc['instance'] as $sign => $comments)
{
	echo "\n####  $sign\n\n$comments";
}


// ############################################################
// Now for some examples, these should make things a little clearer

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
EOT;


// ############################################################
// Run and print the examples

echo "\n## Some Examples\n";

// Yay for this hack!
eval(substr($file, 5));

foreach ($examples as $title => $code)
{
	echo "\n### $title\n\n";
	echo "```php\n<?\n$code\n```\n\n";
	echo "Output:\n\n";
	echo "```xml\n";
	eval($code);
	echo "```\n";
}

echo "\n_Automatically generated by $argv[0]_\n";


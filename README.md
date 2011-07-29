# DOM Creator for PHP

A limited but simple API for creating DOMDocumentS in PHP.


## Supports

- Elements that contain text
- Elements that contain other elements
- Namespaces, as long as the whole fragment is of the same one
- No attributes, though they can conceivably be implemented


## Example 1

    $foo = DomCreator::create('http://example.com/foo', 'f', 'foo');

    $foo->ident->name = 'My Name';
    $foo->ident->number = '1234';
    $foo->content = 'Here is the content..';

    $fooDom = $foo->getDocument();
    $fooDom->formatOutput = true;
    echo $fooDom->saveXml();

Ouput:

    <f:foo xmlns:f="http://example.com/foo">
      <f:ident>
        <f:name>My Name</f:name>
        <f:number>1234</f:number>
      </f:ident>
      <f:content>Here is the content..</f:content>
    </f:foo>


## Example 2

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

Output:

    <Bar>
      <One>1</One>
      <Two>2</Two>
      <Three>3</Three>
      <Sub>
        <A>Letter A</A>
        <B>Letter B</B>
        <C>Letter C</C>
      </Sub>
    </Bar>


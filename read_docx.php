<?php
function read_docx($filename){
    $zip = new ZipArchive;
    $text = '';
    if ($zip->open($filename) === TRUE) {
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        $dom = new DOMDocument();
        $dom->loadXML($xml, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
        $elements = $dom->getElementsByTagName('t');
        foreach ($elements as $element) {
            $text .= $element->nodeValue . ' ';
        }
    }
    return $text;
}
echo "--- about ---\n" . read_docx('team collab/advincula/about.docx') . "\n";
echo "--- contact ---\n" . read_docx('team collab/advincula/contact.docx') . "\n";
echo "--- features ---\n" . read_docx('team collab/advincula/features.docx') . "\n";
echo "--- homepage ---\n" . read_docx('team collab/advincula/homepage.docx') . "\n";

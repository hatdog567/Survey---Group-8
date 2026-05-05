function Get-DocxText {
    param($Path)
    $tempZip = "$Path.zip"
    $tempDir = "$Path`_extracted"
    Copy-Item -Path $Path -Destination $tempZip -Force
    Expand-Archive -Path $tempZip -DestinationPath $tempDir -Force
    [xml]$xml = Get-Content "$tempDir\word\document.xml"
    $ns = New-Object Xml.XmlNamespaceManager $xml.NameTable
    $ns.AddNamespace("w", "http://schemas.openxmlformats.org/wordprocessingml/2006/main")
    
    $paragraphs = $xml.SelectNodes("//w:p", $ns)
    $output = @()
    foreach ($p in $paragraphs) {
        $texts = $p.SelectNodes(".//w:t", $ns)
        if ($texts) {
            $paraText = ($texts | ForEach-Object { $_.InnerText }) -join ""
            if ($paraText.Trim() -ne "") {
                $output += $paraText
            }
        }
    }
    
    Remove-Item $tempZip -Force
    Remove-Item $tempDir -Recurse -Force
    return $output -join "`n"
}

Write-Host "--- ABOUT ---"
Get-DocxText "team collab\advincula\about.docx"
Write-Host "`n--- CONTACT ---"
Get-DocxText "team collab\advincula\contact.docx"
Write-Host "`n--- FEATURES ---"
Get-DocxText "team collab\advincula\features.docx"
Write-Host "`n--- HOMEPAGE ---"
Get-DocxText "team collab\advincula\homepage.docx"

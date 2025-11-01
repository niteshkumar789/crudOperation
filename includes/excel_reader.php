<?php
function readExcelFile($filePath)
{
    $ext = pathinfo($filePath, PATHINFO_EXTENSION);
    $rows = [];

    if ($ext === 'csv') {
        if (($handle = fopen($filePath, "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $rows[] = $data;
            }
            fclose($handle);
        }
    } else {
        // Simplified parser using built-in zip + XML read
        $zip = new ZipArchive;
        if ($zip->open($filePath) === TRUE) {
            $xml = $zip->getFromName('xl/worksheets/sheet1.xml');
            $zip->close();
            $xml = simplexml_load_string($xml);
            foreach ($xml->sheetData->row as $row) {
                $r = [];
                foreach ($row->c as $c) {
                    $r[] = (string)$c->v;
                }
                $rows[] = $r;
            }
        }
    }
    return $rows;
}

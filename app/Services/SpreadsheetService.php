<?php

namespace App\Services;

use ZipArchive;
use SimpleXMLElement;
use Exception;

class SpreadsheetService
{
    /**
     * Parse an uploaded file (.xlsx or .csv) into an array of associative rows.
     *
     * @param  string  $filePath
     * @param  string|null  $extension
     * @return array   List of associative arrays where keys are sanitized header names.
     */
    public static function parse(string $filePath, ?string $extension = null): array
    {
        $ext = strtolower($extension ?: pathinfo($filePath, PATHINFO_EXTENSION));

        if ($ext === 'xlsx') {
            return self::parseXlsx($filePath);
        }

        // Default to CSV
        return self::parseCsv($filePath);
    }

    /**
     * Parse CSV file with automatic separator detection (comma or semicolon).
     */
    public static function parseCsv(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new Exception("Unable to open CSV file for reading.");
        }

        // Detect separator using the first line
        $firstLine = fgets($handle);
        rewind($handle);

        $delimiter = ',';
        if ($firstLine !== false && substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
            $delimiter = ';';
        }

        // Handle possible UTF-8 BOM
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $headers = [];
        $rows    = [];

        while (($data = fgetcsv($handle, 4096, $delimiter)) !== false) {
            // Skip empty rows
            if (empty(array_filter($data, fn($v) => trim($v) !== ''))) {
                continue;
            }

            if (empty($headers)) {
                // First non-empty row is header
                $headers = array_map(fn($h) => strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '_', $h))), $data);
                continue;
            }

            $row = [];
            foreach ($headers as $index => $colName) {
                if ($colName !== '') {
                    $row[$colName] = isset($data[$index]) ? trim($data[$index]) : null;
                }
            }
            $rows[] = $row;
        }

        fclose($handle);
        return $rows;
    }

    /**
     * Parse XLSX OpenXML format using built-in PHP ZipArchive and SimpleXML.
     */
    public static function parseXlsx(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new Exception("Unable to open XLSX archive. Ensure file is a valid Excel spreadsheet.");
        }

        // 1. Read shared strings if present
        $sharedStrings = [];
        $stringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($stringsXml !== false) {
            $xml = simplexml_load_string($stringsXml);
            if ($xml) {
                foreach ($xml->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string) $si->t;
                    } elseif (isset($si->r)) {
                        $text = '';
                        foreach ($si->r as $r) {
                            $text .= (string) $r->t;
                        }
                        $sharedStrings[] = $text;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        // 2. Read first worksheet (xl/worksheets/sheet1.xml)
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml === false) {
            $zip->close();
            throw new Exception("No worksheet found in XLSX file.");
        }

        $xml = simplexml_load_string($sheetXml);
        $zip->close();

        if (!$xml || !isset($xml->sheetData->row)) {
            return [];
        }

        $rawRows = [];
        foreach ($xml->sheetData->row as $r) {
            $rowValues = [];
            foreach ($r->c as $c) {
                // Extract column letter from cell reference, e.g. A1, B1, AA1
                $cellRef = (string) $c['r'];
                preg_match('/^([A-Z]+)(\d+)$/', $cellRef, $matches);
                $colLetter = $matches[1] ?? '';
                $colIndex  = self::columnLetterToIndex($colLetter);

                $cellType = (string) $c['t'];
                $val = '';

                if ($cellType === 's') {
                    // Shared string reference index
                    $stringIndex = (int) $c->v;
                    $val = $sharedStrings[$stringIndex] ?? '';
                } elseif ($cellType === 'inlineStr' && isset($c->is->t)) {
                    $val = (string) $c->is->t;
                } elseif (isset($c->v)) {
                    $val = (string) $c->v;
                }

                $rowValues[$colIndex] = trim($val);
            }

            // Fill gaps up to highest index
            if (!empty($rowValues)) {
                $maxIndex = max(array_keys($rowValues));
                $normalizedRow = [];
                for ($i = 0; $i <= $maxIndex; $i++) {
                    $normalizedRow[$i] = $rowValues[$i] ?? '';
                }
                $rawRows[] = $normalizedRow;
            }
        }

        if (empty($rawRows)) {
            return [];
        }

        // First row is header
        $headerRow = array_shift($rawRows);
        $headers   = array_map(fn($h) => strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '_', $h))), $headerRow);

        $parsed = [];
        foreach ($rawRows as $row) {
            // Skip completely empty rows
            if (empty(array_filter($row, fn($v) => trim($v) !== ''))) {
                continue;
            }

            $mapped = [];
            foreach ($headers as $index => $colName) {
                if ($colName !== '') {
                    $mapped[$colName] = isset($row[$index]) ? trim($row[$index]) : null;
                }
            }
            $parsed[] = $mapped;
        }

        return $parsed;
    }

    /**
     * Convert Excel column letters (A, B, ..., Z, AA, AB) to 0-based integer index.
     */
    private static function columnLetterToIndex(string $letters): int
    {
        $index = 0;
        $len = strlen($letters);
        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($letters[$i]) - ord('A') + 1);
        }
        return max(0, $index - 1);
    }
}

<?php
/**
 * exportar_lib.php - Genera y envía un Excel .xlsx REAL (sin librerías externas),
 * usando ZipArchive. Si ZipArchive no está disponible, cae a CSV UTF-8 (con BOM).
 *
 * Uso:
 *   require_once '../../perfil_usuario/exportar_lib.php';
 *   enviar_excel('personal_20260701', ['DNI/CEX','Nombre',...], [ ['0086...','MADUEÑO...'], ... ]);
 *
 * Todas las celdas se escriben como TEXTO (inlineStr) para no perder ceros a la
 * izquierda de los documentos ni reformatear fechas.
 */

if (!function_exists('enviar_excel')):

function _xlsx_col($n) {            // 0 -> A, 1 -> B, 26 -> AA ...
    $s = '';
    for ($n = $n + 1; $n > 0; $n = intdiv($n - 1, 26)) {
        $s = chr(65 + (($n - 1) % 26)) . $s;
    }
    return $s;
}

function _xlsx_esc($v) {
    $v = (string)$v;
    // quitar caracteres de control no válidos en XML
    $v = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $v);
    return htmlspecialchars($v, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

function enviar_excel($base, array $headers, array $rows) {
    if (class_exists('ZipArchive')) {
        _enviar_xlsx($base . '.xlsx', $headers, $rows);
    } else {
        _enviar_csv($base . '.csv', $headers, $rows);
    }
}

function _enviar_xlsx($archivo, array $headers, array $rows) {
    // --- Hoja ---
    $sheet  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
    $sheet .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';

    $sheet .= '<row r="1">';
    foreach (array_values($headers) as $i => $h) {
        $sheet .= '<c r="' . _xlsx_col($i) . '1" t="inlineStr" s="1"><is><t xml:space="preserve">' . _xlsx_esc($h) . '</t></is></c>';
    }
    $sheet .= '</row>';

    $r = 1;
    foreach ($rows as $row) {
        $r++;
        $sheet .= '<row r="' . $r . '">';
        $i = 0;
        foreach ($row as $val) {
            $sheet .= '<c r="' . _xlsx_col($i) . $r . '" t="inlineStr"><is><t xml:space="preserve">' . _xlsx_esc($val) . '</t></is></c>';
            $i++;
        }
        $sheet .= '</row>';
    }
    $sheet .= '</sheetData></worksheet>';

    $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
        . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
        . '<Default Extension="xml" ContentType="application/xml"/>'
        . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
        . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
        . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
        . '</Types>';

    $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
        . '</Relationships>';

    $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        . '<sheets><sheet name="Datos" sheetId="1" r:id="rId1"/></sheets></workbook>';

    $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
        . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
        . '</Relationships>';

    // Estilo 1 = encabezado (negrita, texto blanco, fondo azul marino)
    $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
        . '<fonts count="2">'
        . '<font><sz val="11"/><name val="Calibri"/></font>'
        . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
        . '</fonts>'
        . '<fills count="3">'
        . '<fill><patternFill patternType="none"/></fill>'
        . '<fill><patternFill patternType="gray125"/></fill>'
        . '<fill><patternFill patternType="solid"><fgColor rgb="FF1E3A8A"/><bgColor indexed="64"/></patternFill></fill>'
        . '</fills>'
        . '<borders count="1"><border/></borders>'
        . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
        . '<cellXfs count="2">'
        . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
        . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'
        . '</cellXfs>'
        . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
        . '</styleSheet>';

    $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
    $zip = new ZipArchive();
    $zip->open($tmp, ZipArchive::OVERWRITE);
    $zip->addFromString('[Content_Types].xml', $contentTypes);
    $zip->addFromString('_rels/.rels', $rels);
    $zip->addFromString('xl/workbook.xml', $workbook);
    $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);
    $zip->addFromString('xl/styles.xml', $styles);
    $zip->addFromString('xl/worksheets/sheet1.xml', $sheet);
    $zip->close();

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $archivo . '"');
    header('Content-Length: ' . filesize($tmp));
    header('Pragma: no-cache');
    header('Expires: 0');
    readfile($tmp);
    @unlink($tmp);
    exit;
}

function _enviar_csv($archivo, array $headers, array $rows) {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $archivo . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    echo "\xEF\xBB\xBF"; // BOM (acentos correctos en Excel)
    $out = fopen('php://output', 'w');
    fputcsv($out, $headers);
    foreach ($rows as $row) fputcsv($out, $row);
    fclose($out);
    exit;
}

endif;

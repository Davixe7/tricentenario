<?php

require_once __DIR__ . '/../Models/Template.php';

class TemplateController {
    private $templateModel;

    // Configuración centralizada de las 4 plantillas
    private $types = [
        'reporte-semanal' => [
            'key' => 'reporte_semanal',
            'title' => '1. Reporte Semanal de Despachadores',
            'tab_title' => 'Reporte Semanal',
            'filename' => '1_Reporte_Semanal_Despachadores.xlsx',
            'has_semester' => false,
            'date_format_note' => false
        ],
        'bitacora-incidencias' => [
            'key' => 'bitacora_incidencias',
            'title' => '2. Bitácora de Incidencias',
            'tab_title' => 'Bitácora de Incidencias',
            'filename' => '2_Bitacora_de_Incidencias.xlsx',
            'has_semester' => false,
            'date_format_note' => true
        ],
        'calificaciones-360' => [
            'key' => 'calificaciones_360',
            'title' => '3. Calificaciones 360º',
            'tab_title' => 'Calificaciones 360º',
            'filename' => '3_Calificaciones_360.xlsx',
            'has_semester' => true,
            'date_format_note' => false
        ],
        'proyecto-semestre' => [
            'key' => 'proyecto_semestre',
            'title' => '4. Proyecto Semestre',
            'tab_title' => 'Proyecto Semestre',
            'filename' => '4_Proyecto_Semestre.xlsx',
            'has_semester' => true,
            'date_format_note' => false
        ]
    ];

    public function __construct() {
        Auth::check();
        $this->templateModel = new Template();
    }

    public function reporteSemanal() {
        $this->renderView('reporte-semanal');
    }

    public function bitacoraIncidencias() {
        $this->renderView('bitacora-incidencias');
    }

    public function calificaciones360() {
        $this->renderView('calificaciones-360');
    }

    public function proyectoSemestre() {
        $this->renderView('proyecto-semestre');
    }

    private function renderView($slug) {
        $config = $this->types[$slug] ?? null;
        if (!$config) {
            header('Location: ' . BASE_URL);
            exit;
        }

        $semester = $_GET['semester'] ?? null;
        $lastLog = $this->templateModel->getLastLog($config['key'], $semester);

        require_once __DIR__ . '/../Views/layout/header.php';
        require_once __DIR__ . '/../Views/templates/show.php';
        require_once __DIR__ . '/../Views/layout/footer.php';
    }

    // Descarga directa de archivos de plantilla .xlsx
    public function download($slug) {
        $config = $this->types[$slug] ?? null;
        if (!$config) {
            header('Location: ' . BASE_URL);
            exit;
        }

        $filePath = __DIR__ . '/../../public/assets/templates/' . $config['filename'];
        if (file_exists($filePath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        } else {
            die('El archivo de la plantilla no se encuentra disponible.');
        }
    }

    // Carga de archivo Excel y registro de log
    public function upload() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
                die('Invalid CSRF Token');
            }

            $slug = $_POST['template_slug'] ?? '';
            $config = $this->types[$slug] ?? null;

            if (!$config) {
                header('Location: ' . BASE_URL);
                exit;
            }

            $semester = $config['has_semester'] ? ($_POST['semester'] ?? null) : null;

            if ($config['has_semester'] && empty($semester)) {
                header('Location: ' . BASE_URL . '/templates/' . $slug . '?error=semester_required');
                exit;
            }

            if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION));
                if ($ext !== 'xlsx' && $ext !== 'xls') {
                    header('Location: ' . BASE_URL . '/templates/' . $slug . '?error=invalid_file');
                    exit;
                }

                $uploadDir = UPLOAD_DIR . 'templates/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $filename = $config['key'] . ($semester ? '_sem' . $semester : '') . '_' . time() . '.' . $ext;
                $targetFile = $uploadDir . $filename;

                if (move_uploaded_file($_FILES['excel_file']['tmp_name'], $targetFile)) {
                    // Contar número de filas del Excel nativamente
                    $rowCount = $this->countExcelRows($targetFile);
                    
                    $userId = $_SESSION['user']['id'] ?? null;
                    $relativePath = 'uploads/templates/' . $filename;

                    $this->templateModel->saveLog($config['key'], $rowCount, $relativePath, $userId, $semester);

                    header('Location: ' . BASE_URL . '/templates/' . $slug . '?success=1&count=' . $rowCount . ($semester ? '&semester=' . $semester : ''));
                    exit;
                }
            }
        }
        header('Location: ' . BASE_URL);
        exit;
    }

    // Conteo nativo de filas utilizando ZipArchive y SimpleXML 
    private function countExcelRows($filePath) {
        if (!class_exists('ZipArchive')) {
            return 0;
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) === TRUE) {
            $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
            $zip->close();

            if ($sheetXml) {
                $xml = simplexml_load_string($sheetXml);
                if ($xml && isset($xml->sheetData->row)) {
                    $totalRows = count($xml->sheetData->row);
                    // Resta la fila de encabezado
                    return max(0, $totalRows - 1);
                }
            }
        }
        return 0;
    }
}
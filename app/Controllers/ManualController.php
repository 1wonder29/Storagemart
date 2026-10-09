<?php

require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../Helpers/ManualPresentation.php';
require_once __DIR__ . '/../Helpers/TicketManuals.php';

/**
 * Troubleshooting guides for everyone who is signed in, hosted by the TMS itself
 * (the Canva link needed a Canva login): slides in the browser, the .pptx, or the
 * PowerPoint / PDF that IT uploaded.
 */
class ManualController extends AuthController
{
    /** GET /manuals/view?id= */
    public function view(): void
    {
        $manual = $this->load();
        $slides = ManualPresentation::slides($manual);
        $base = rtrim(BASE_URL, '/');
        $hasFile = !empty($manual['file_path']) && TicketManuals::filePath((string) $manual['file_path']) !== null;
        $isPdf = $hasFile && ($manual['file_mime'] ?? '') === 'application/pdf';
        require __DIR__ . '/../Views/manuals/view.php';
    }

    /** GET /manuals/pptx?id= : the slides as a PowerPoint file. */
    public function pptx(): void
    {
        $manual = $this->load();
        $data = ManualPresentation::pptx($manual);
        $name = trim(preg_replace('/[^A-Za-z0-9 _-]+/', '', (string) $manual['title'])) ?: 'IT guide';
        header('Content-Type: application/vnd.openxmlformats-officedocument.presentationml.presentation');
        header('Content-Disposition: attachment; filename="' . $name . '.pptx"');
        header('Content-Length: ' . strlen($data));
        echo $data;
        exit;
    }

    /** GET /manuals/file?id= : the PowerPoint / PDF uploaded by IT or Admin. */
    public function file(): void
    {
        $manual = $this->load();
        $path = !empty($manual['file_path']) ? TicketManuals::filePath((string) $manual['file_path']) : null;
        if ($path === null) {
            http_response_code(404);
            echo 'File not found.';
            exit;
        }
        $mime = (string) $manual['file_mime'];
        $inline = $mime === 'application/pdf';
        header('Content-Type: ' . ($inline ? 'application/pdf' : $mime));
        header('Content-Length: ' . filesize($path));
        header('X-Content-Type-Options: nosniff');
        header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="'
            . str_replace(['"', "\r", "\n"], '', (string) $manual['file_name']) . '"');
        readfile($path);
        exit;
    }

    private function load(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['account_id'])) {
            $this->redirect('/login');
            exit;
        }
        global $pdo;
        $manual = null;
        try {
            $stmt = $pdo->prepare('SELECT * FROM tblit_manuals WHERE manual_id = ? LIMIT 1');
            $stmt->execute([(int) ($_GET['id'] ?? 0)]);
            $manual = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Throwable $e) {
            $manual = null;
        }
        if (!$manual) {
            http_response_code(404);
            echo '<!DOCTYPE html><meta charset="utf-8"><body style="font-family:sans-serif;padding:40px">Guide not found.'
                . ' <a href="javascript:history.back()">Go back</a></body>';
            exit;
        }
        return $manual;
    }
}

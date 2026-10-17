<?php

require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../Helpers/ManualPresentation.php';
require_once __DIR__ . '/../Helpers/TicketManuals.php';

/**
 * IT manuals for everyone who is signed in, hosted by the TMS itself (the Canva folder
 * needed a Canva login): the library, each guide as slides in the IT Department's manual
 * design, the .pptx, step photos, and the PowerPoint / PDF that IT uploaded.
 */
class ManualController extends AuthController
{
    /** GET /manuals : all guides by group, with search. */
    public function library(): void
    {
        $this->requireLogin();
        global $pdo;
        $manuals = array_values(array_filter(TicketManuals::all($pdo), [TicketManuals::class, 'canView']));
        $groups = [];
        foreach ($manuals as $manual) {
            $groups[TicketManuals::groupOf($manual)][] = $manual;
        }
        $base = rtrim(BASE_URL, '/');
        $role = strtoupper((string) ($_SESSION['superuser_real_usertype'] ?? $_SESSION['usertype'] ?? ''));
        $canManage = in_array($role, ['IT', 'ADMIN'], true);
        $manageUrl = $role === 'IT' ? '/it/manuals' : '/admin/manuals';
        // "How to use the TMS" guides: your own role first; IT and Admin see every role's guide.
        require_once __DIR__ . '/../Helpers/TmsGuide.php';
        $myGuideRole = TmsGuide::currentRole();
        $guideRoles = array_values(array_filter(array_keys(TmsGuide::ROLES), static function (string $key) use ($myGuideRole): bool {
            return TmsGuide::canOpen($myGuideRole, $key);
        }));
        usort($guideRoles, static function (string $a, string $b) use ($myGuideRole): int {
            return ($b === $myGuideRole) <=> ($a === $myGuideRole);
        });
        require __DIR__ . '/../Views/manuals/library.php';
    }

    /** GET /manuals/view?id= */
    public function view(): void
    {
        $manual = $this->load();
        $slides = ManualPresentation::slides($manual, [TicketManuals::class, 'stepPhotoPath']);
        $base = rtrim(BASE_URL, '/');
        $hasFile = !empty($manual['file_path']) && TicketManuals::filePath((string) $manual['file_path']) !== null;
        $isPdf = $hasFile && ($manual['file_mime'] ?? '') === 'application/pdf';
        require __DIR__ . '/../Views/manuals/view.php';
    }

    /** GET /manuals/pptx?id= : the slides as a PowerPoint file. */
    public function pptx(): void
    {
        $manual = $this->load();
        $data = ManualPresentation::pptx($manual, [TicketManuals::class, 'stepPhotoPath']);
        $name = trim(preg_replace('/[^A-Za-z0-9 _()-]+/', '', (string) $manual['title'])) ?: 'IT guide';
        header('Content-Type: application/vnd.openxmlformats-officedocument.presentationml.presentation');
        header('Content-Disposition: attachment; filename="' . $name . '.pptx"');
        header('Content-Length: ' . strlen($data));
        echo $data;
        exit;
    }

    /** GET /manuals/photo?id=&step= : the photo of one step. */
    public function photo(): void
    {
        $manual = $this->load();
        $photos = ManualPresentation::stepPhotos($manual);
        $file = $photos[(int) ($_GET['step'] ?? 0)] ?? '';
        $path = $file !== '' ? TicketManuals::stepPhotoPath($file) : null;
        if ($path === null) {
            http_response_code(404);
            echo 'Photo not found.';
            exit;
        }
        header('Content-Type: ' . (substr($path, -4) === '.png' ? 'image/png' : 'image/jpeg'));
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: private, max-age=86400');
        header('X-Content-Type-Options: nosniff');
        readfile($path);
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

    private function requireLogin(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['account_id'])) {
            $this->redirect('/login');
            exit;
        }
    }

    private function load(): array
    {
        $this->requireLogin();
        global $pdo;
        $manual = null;
        try {
            $stmt = $pdo->prepare('SELECT * FROM tblit_manuals WHERE manual_id = ? LIMIT 1');
            $stmt->execute([(int) ($_GET['id'] ?? 0)]);
            $manual = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Throwable $e) {
            $manual = null;
        }
        if (!$manual || !TicketManuals::canView($manual)) {
            http_response_code(404);
            echo '<!DOCTYPE html><meta charset="utf-8"><body style="font-family:sans-serif;padding:40px">Guide not found.'
                . ' <a href="javascript:history.back()">Go back</a></body>';
            exit;
        }
        return $manual;
    }
}

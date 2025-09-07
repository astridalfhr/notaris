<?php
// app/Controllers/Admin/WorkController.php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\WorkFileModel;
use CodeIgniter\Files\File as LocalFile;

class WorkController extends BaseController
{
    // Allowed categories & subtypes
    private array $map = [
        'PPAT' => ['AJB', 'Hibah', 'Turun Waris', 'APHT', 'PPJB'],
        'Notaris' => ['CV', 'PT', 'Pergantian Pengurus', 'PJB', 'SKMHT', 'Waarmerking', 'Legalisasi'],
    ];

    private function storageDir(): string
    {
        $dir = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'kerja';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        return $dir;
    }

    private function validCat(string $cat): bool
    {
        return isset($this->map[$cat]);
    }

    private function validSubtype(string $cat, string $st): bool
    {
        return $this->validCat($cat) && in_array($st, $this->map[$cat], true);
    }

    private function safeDetectMime(string $path, string $fallback = 'application/octet-stream'): string
    {
        if (is_file($path)) {
            try {
                $f = new LocalFile($path);
                $mt = $f->getMimeType();
                if ($mt)
                    return $mt;
            } catch (\Throwable $e) {
                // ignore
            }
            $mt = @mime_content_type($path);
            if ($mt)
                return $mt;
        }
        return $fallback;
    }

    // GET /admin/kerja/feed?category=PPAT|Notaris
    public function feed()
    {
        $cat = (string) ($this->request->getGet('category') ?? '');
        $m = new WorkFileModel();

        if ($cat && $this->validCat($cat)) {
            $rows = $m->where('category', $cat)
                ->orderBy('subtype', 'ASC')
                ->orderBy('created_at', 'DESC')
                ->findAll();
        } else {
            $rows = $m->orderBy('category', 'ASC')
                ->orderBy('subtype', 'ASC')
                ->orderBy('created_at', 'DESC')
                ->findAll();
        }

        $grouped = [];
        foreach ($rows as $r) {
            $key = ($r['category'] ?? '') . '|' . ($r['subtype'] ?? '');
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'category' => $r['category'],
                    'subtype' => $r['subtype'],
                    'items' => [],
                ];
            }
            $grouped[$key]['items'][] = [
                'id' => (int) $r['id'],
                'title' => (string) ($r['title'] ?? ''),
                'notes' => (string) ($r['notes'] ?? ''),
                'filename' => (string) ($r['filename'] ?? ''),
                'mime' => (string) ($r['mime'] ?? ''),
                'size' => (int) ($r['size'] ?? 0),
                'created_at' => (string) ($r['created_at'] ?? ''),
                'url_download' => site_url('admin/kerja/download/' . $r['id']),
                'url_preview' => site_url('admin/kerja/preview/' . $r['id']),
            ];
        }

        return $this->response->setJSON([
            'ok' => true,
            'map' => $this->map,
            'groups' => array_values($grouped),
        ]);
    }

    // POST /admin/kerja/upload (multipart)
    public function upload()
    {
        $cat = trim((string) $this->request->getPost('category'));
        $sub = trim((string) $this->request->getPost('subtype'));
        $ttl = trim((string) $this->request->getPost('title'));
        $note = trim((string) $this->request->getPost('notes'));

        if (!$this->validSubtype($cat, $sub)) {
            return $this->response->setStatusCode(422)
                ->setJSON(['ok' => false, 'error' => 'Invalid category/subtype']);
        }

        $file = $this->request->getFile('file');
        if (!$file || !$file->isValid()) {
            return $this->response->setStatusCode(400)
                ->setJSON(['ok' => false, 'error' => 'File is required']);
        }

        // Read client info BEFORE move (temp file will be gone after move)
        $clientName = $file->getClientName();
        $clientMime = $file->getClientMimeType() ?: 'application/octet-stream';
        $clientSize = (int) $file->getSize();

        $dir = $this->storageDir();
        $newName = $file->getRandomName();

        if (!$file->move($dir, $newName)) {
            return $this->response->setStatusCode(500)
                ->setJSON(['ok' => false, 'error' => 'Failed to move uploaded file. Check write permissions.']);
        }

        $target = $dir . DIRECTORY_SEPARATOR . $newName;
        $realMime = $this->safeDetectMime($target, $clientMime);
        $realSize = is_file($target) ? filesize($target) : $clientSize;

        $m = new WorkFileModel();
        $id = $m->insert([
            'category' => $cat,
            'subtype' => $sub,
            'title' => $ttl !== '' ? $ttl : $clientName,
            'notes' => $note,
            'filename' => $newName,
            'mime' => $realMime,
            'size' => $realSize,
            'uploaded_by' => (string) (session()->get('email') ?? session()->get('username') ?? 'admin'),
        ], true);

        return $this->response->setJSON(['ok' => true, 'id' => (int) $id]);
    }

    // POST /admin/kerja/update/{id} (multipart optional file)
    public function update($id)
    {
        $id = (int) $id;
        $m = new WorkFileModel();
        $row = $m->find($id);
        if (!$row) {
            return $this->response->setStatusCode(404)->setJSON(['ok' => false, 'error' => 'Not found']);
        }

        $cat = trim((string) $this->request->getPost('category'));
        $sub = trim((string) $this->request->getPost('subtype'));
        $ttl = trim((string) $this->request->getPost('title'));
        $note = trim((string) $this->request->getPost('notes'));

        if ($cat && $sub && !$this->validSubtype($cat, $sub)) {
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'error' => 'Invalid category/subtype']);
        }

        $payload = [];
        if ($cat)
            $payload['category'] = $cat;
        if ($sub)
            $payload['subtype'] = $sub;
        if ($ttl !== '')
            $payload['title'] = $ttl;
        $payload['notes'] = $note;

        $file = $this->request->getFile('file');
        if ($file && $file->isValid()) {
            // Capture before move
            $clientMime = $file->getClientMimeType() ?: 'application/octet-stream';

            $dir = $this->storageDir();
            $newName = $file->getRandomName();

            if (!$file->move($dir, $newName)) {
                return $this->response->setStatusCode(500)
                    ->setJSON(['ok' => false, 'error' => 'Failed to move uploaded file.']);
            }

            // Delete old file
            $old = $dir . DIRECTORY_SEPARATOR . (string) $row['filename'];
            if (is_file($old)) {
                @unlink($old);
            }

            $target = $dir . DIRECTORY_SEPARATOR . $newName;
            $payload['filename'] = $newName;
            $payload['mime'] = $this->safeDetectMime($target, $clientMime);
            $payload['size'] = is_file($target) ? filesize($target) : (int) $file->getSize();
        }

        $m->update($id, $payload);
        return $this->response->setJSON(['ok' => true]);
    }

    // DELETE /admin/kerja/delete/{id}
    public function delete($id)
    {
        $id = (int) $id;
        $m = new WorkFileModel();
        $row = $m->find($id);
        if ($row) {
            $path = $this->storageDir() . DIRECTORY_SEPARATOR . $row['filename'];
            if (is_file($path)) {
                @unlink($path);
            }
            $m->delete($id);
        }
        return $this->response->setJSON(['ok' => true]);
    }

    // GET /admin/kerja/download/{id}
    public function download($id)
    {
        $id = (int) $id;
        $m = new WorkFileModel();
        $row = $m->find($id);
        if (!$row) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $path = $this->storageDir() . DIRECTORY_SEPARATOR . $row['filename'];
        $name = (string) ($row['title'] ?: $row['filename']);
        return $this->response->download($path, null)->setFileName($name);
    }

    // GET /admin/kerja/preview/{id}
    public function preview($id)
    {
        $id = (int) $id;
        $m = new WorkFileModel();
        $row = $m->find($id);
        if (!$row) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $path = $this->storageDir() . DIRECTORY_SEPARATOR . $row['filename'];
        $mime = strtolower((string) ($row['mime'] ?? 'application/octet-stream'));
        $inline = str_contains($mime, 'pdf') || str_contains($mime, 'image/');

        if ($inline && is_file($path)) {
            $this->response->setHeader('Content-Type', $mime);
            $this->response->setHeader('Content-Disposition', 'inline; filename="' . ($row['title'] ?: $row['filename']) . '"');
            return $this->response->setBody(file_get_contents($path));
        }

        return $this->download($id);
    }
}

<?php 
namespace App\Controllers\Multiuser;

use App\Controllers\BaseController;
use App\Models\SiteNewsModel;
use CodeIgniter\I18n\Time;

class News extends BaseController
{
    public function index()
    {
        if (!session('id')) return redirect()->to('/login');

        $m = new SiteNewsModel();
        $rows = $m->orderBy('published_at','DESC')->orderBy('id','DESC')->findAll(100);
        return view('multiuser/news_index', ['rows'=>$rows]);
    }

    public function create()
    {
        return view('multiuser/news_form', ['mode'=>'create','row'=>[]]);
    }

    public function store()
    {
        $m = new SiteNewsModel();
        $title = trim((string)$this->request->getPost('title'));
        $slug  = url_title($title, '-', true);

        $payload = [
            'title' => $title,
            'slug'  => $slug,
            'excerpt' => trim((string)$this->request->getPost('excerpt')),
            'body'    => (string)$this->request->getPost('body'),
            'is_featured'  => (int)$this->request->getPost('is_featured'),
            'is_published' => (int)$this->request->getPost('is_published', FILTER_SANITIZE_NUMBER_INT) ?: 1,
            'published_at' => $this->request->getPost('published_at') ?: Time::now()->toDateTimeString(),
        ];

        // upload image (opsional)
        $img = $this->request->getFile('image');
        if ($img && $img->isValid()) {
            $newName = $img->getRandomName();
            $img->move(FCPATH.'images/news', $newName);
            $payload['image'] = $newName;
        }

        $m->insert($payload);
        return redirect()->to(site_url('multiuser/news'))->with('success','Berita ditambahkan.');
    }

    public function edit(int $id)
    {
        $m = new SiteNewsModel();
        $row = $m->find($id);
        if (!$row) return redirect()->to(site_url('multiuser/news'))->with('error','Berita tidak ditemukan.');
        return view('multiuser/news_form', ['mode'=>'edit','row'=>$row]);
    }

    public function update(int $id)
    {
        $m = new SiteNewsModel();
        $row = $m->find($id);
        if (!$row) return redirect()->to(site_url('multiuser/news'))->with('error','Berita tidak ditemukan.');

        $title = trim((string)$this->request->getPost('title'));
        $slug  = $row['slug'] ?: url_title($title, '-', true);

        $payload = [
            'title' => $title,
            'slug'  => $slug,
            'excerpt' => trim((string)$this->request->getPost('excerpt')),
            'body'    => (string)$this->request->getPost('body'),
            'is_featured'  => (int)$this->request->getPost('is_featured'),
            'is_published' => (int)$this->request->getPost('is_published', FILTER_SANITIZE_NUMBER_INT) ?: 1,
            'published_at' => $this->request->getPost('published_at') ?: $row['published_at'],
        ];

        $img = $this->request->getFile('image');
        if ($img && $img->isValid()) {
            $newName = $img->getRandomName();
            $img->move(FCPATH.'images/news', $newName);
            $payload['image'] = $newName;
        }

        $m->update($id, $payload);
        return redirect()->to(site_url('multiuser/news'))->with('success','Berita diperbarui.');
    }

    public function delete(int $id)
    {
        $m = new SiteNewsModel();
        $m->delete($id);
        return redirect()->to(site_url('multiuser/news'))->with('success','Berita dihapus.');
    }

    public function toggleFeature(int $id)
    {
        $m = new SiteNewsModel();
        $row = $m->find($id);
        if ($row) {
            $m->update($id, ['is_featured' => $row['is_featured'] ? 0 : 1]);
        }
        return redirect()->to(site_url('multiuser/news'));
    }

    public function togglePublish(int $id)
    {
        $m = new SiteNewsModel();
        $row = $m->find($id);
        if ($row) {
            $m->update($id, [
                'is_published' => $row['is_published'] ? 0 : 1,
                'published_at' => $row['is_published'] ? $row['published_at'] : date('Y-m-d H:i:s'),
            ]);
        }
        return redirect()->to(site_url('multiuser/news'));
    }
}

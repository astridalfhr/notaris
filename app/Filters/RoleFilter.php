<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    protected string $loginUrl = '/login';

    private function normalize(string $v): string
    {
        return strtolower(preg_replace('/[^a-z]/', '', $v));
    }

    private function canon(string $v): string
    {
        $k = $this->normalize($v);
        if (in_array($k, ['admin', 'superadmin', 'staff', 'employee', 'pegawai', 'karyawan'], true))
            return 'admin';
        if (in_array($k, ['multi', 'multiuser'], true))
            return 'multiuser';
        return $k === '' ? 'user' : $k;
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        $ses = session();
        $isLogged = (bool) ($ses->get('id') || $ses->get('user_id') || $ses->get('logged_in') || $ses->get('isLoggedIn'));
        if (!$isLogged) {
            $ses->set('redirect_url', (string) $request->getUri());
            return redirect()->to($this->loginUrl)->with('error', 'Silakan login terlebih dahulu.');
        }
        if (empty($arguments))
            return;
        $roleCanon = $this->canon((string) ($ses->get('role') ?? ''));
        $allowed = array_map(fn($r) => $this->canon((string) $r), (array) $arguments);
        if (!in_array($roleCanon, $allowed, true)) {
            return service('response')->setStatusCode(403)->setBody(view('errors/unauthorized'));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}

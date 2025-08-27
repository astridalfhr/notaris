<?php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class Auth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $ses = session();

        // Anggap login jika salah satu flag/set id ada
        $isLogged = (bool) (
            $ses->get('logged_in') ||
            $ses->get('isLoggedIn') ||
            $ses->get('id') ||
            $ses->get('user_id')
        );

        if (!$isLogged) {
            // Jika request AJAX/JSON, balas 401 agar tidak redirect tak berujung
            if ($request->isAJAX() || $request->getHeaderLine('Accept') === 'application/json') {
                return service('response')->setStatusCode(401);
            }

            // Simpan return URL (opsional)
            $returnUrl = current_url();
            return redirect()->to('/login')->with('returnUrl', $returnUrl)
                ->with('error', 'Silakan login terlebih dulu.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}

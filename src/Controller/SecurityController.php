<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(Request $request, SessionInterface $session): Response|RedirectResponse
    {
        if ($request->isMethod('POST')) {
            $email = strtolower((string) $request->request->get('email', ''));
            $role = 'student';

            if (str_contains($email, 'admin')) {
                $role = 'admin';
            } elseif (str_contains($email, 'teacher')) {
                $role = 'teacher';
            }

            $map = [
                'admin' => ['name' => 'Admin Demo', 'redirect' => 'admin_dashboard'],
                'teacher' => ['name' => 'Jane Teacher', 'redirect' => 'teacher_dashboard'],
                'student' => ['name' => 'Sam Student', 'redirect' => 'student_dashboard'],
            ];

            $session->set('demo_user', ['role' => $role, 'name' => $map[$role]['name']]);
            return $this->redirectToRoute($map[$role]['redirect']);
        }

        return $this->render('security/login.html.twig');
    }

    #[Route('/quick-login/{role}', name: 'app_quick_login')]
    public function quickLogin(string $role, Request $request, SessionInterface $session): RedirectResponse
    {
        $role = strtolower($role);
        $map = [
            'admin' => ['name' => 'Admin Demo', 'redirect' => 'admin_dashboard'],
            'teacher' => ['name' => 'Jane Teacher', 'redirect' => 'teacher_dashboard'],
            'student' => ['name' => 'Sam Student', 'redirect' => 'student_dashboard'],
        ];

        if (!isset($map[$role])) {
            return $this->redirectToRoute('app_login');
        }

        // store demo user info in session for this simple demo
        $session->set('demo_user', ['role' => $role, 'name' => $map[$role]['name']]);

        return $this->redirectToRoute($map[$role]['redirect']);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(SessionInterface $session): RedirectResponse
    {
        $session->remove('demo_user');
        return $this->redirectToRoute('home');
    }
}

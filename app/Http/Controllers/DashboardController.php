<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Redirect ke dashboard sesuai role.
     */
    public function index(Request $request): RedirectResponse
    {
        $role = $request->user()->role;
        $role = $role instanceof UserRole ? $role : UserRole::tryFrom((string) $role) ?? UserRole::Student;

        return redirect()->route($role->dashboardRoute());
    }

    public function superadmin(): View
    {
        return view('dashboard.superadmin');
    }

    public function adminAcademic(): View
    {
        return view('dashboard.admin-academic');
    }

    public function lecturer(): View
    {
        return view('dashboard.lecturer');
    }

    public function student(): View
    {
        return view('dashboard.student');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        $pendingUsers = User::where('admin_approved', false)->get();

        return view('admin.index', compact('pendingUsers'));
    }

    /**
     * Approve a user for access.
     */
    public function approveUser(User $user)
    {
        $user->update(['admin_approved' => true]);

        return redirect()->route('admin.index')->with('success', 'User ' . $user->name . ' has been approved.');
    }

    /**
     * Delete a user.
     */
    public function deleteUser(User $user)
    {
        $userName = $user->name;
        $user->delete();

        return redirect()->route('admin.index')->with('success', 'User ' . $userName . ' has been deleted.');
    }
}
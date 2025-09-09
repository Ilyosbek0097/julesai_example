<?php

namespace App\Http\Controllers;

use App\Models\AnnounceTemplate;
use App\Models\Cashbox; // Assuming this model exists
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AnnounceTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = AnnounceTemplate::query();

        // Cashbox filter
        if ($request->filled('cashbox_id')) {
            $query->where('cashbox_id', $request->input('cashbox_id'));
        }

        // Search filter
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('docnumb', 'like', "%{$searchTerm}%")
                  ->orWhere('clname', 'like', "%{$searchTerm}%")
                  ->orWhere('coname', 'like', "%{$searchTerm}%")
                  ->orWhere('paypurpose', 'like', "%{$searchTerm}%");
            });
        }

        // Date filter
        if ($request->filled('date')) {
            $query->whereDate('currday', $request->input('date'));
        }

        // Get cashboxes for the dropdown based on user role
        $cashboxesQuery = Cashbox::query();

        // I'm assuming the user object has a `hasRole` method and a `local_code` property.
        if ($user && !$user->hasRole('admin')) {
            $cashboxesQuery->where('local_code', $user->local_code);
        }

        $cashboxes = $cashboxesQuery->select('id', 'name', 'local_code')->get()->map(function ($cashbox) {
            return [
                'value' => $cashbox->id,
                'label' => "({$cashbox->local_code}) {$cashbox->name}",
            ];
        });

        $templates = $query->orderBy('currday', 'desc')->paginate(15)->withQueryString();

        return Inertia::render('AnnounceTemplates/Index', [
            'templates' => $templates,
            'cashboxes' => $cashboxes,
            'filters' => $request->only(['search', 'date', 'cashbox_id']),
        ]);
    }
}

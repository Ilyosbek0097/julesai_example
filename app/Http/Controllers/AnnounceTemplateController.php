<?php

namespace App\Http\Controllers;

use App\Models\AnnounceTemplate;
use App\Models\Cashbox; // Assuming this model exists
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Exports\AnnounceTemplatesExport;
use Maatwebsite\Excel\Facades\Excel;

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

    /**
     * Export selected announce templates to an Excel file.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:announce_templates,announce_template_id',
        ]);

        $templates = AnnounceTemplate::with('cashbox')->whereIn('announce_template_id', $request->input('ids'))->get();

        return Excel::download(new AnnounceTemplatesExport($templates), 'e_lonlar.xlsx');
    }

    /**
     * Return a print-friendly HTML view of the selected templates.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function print(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:announce_templates,announce_template_id',
        ]);

        $templates = AnnounceTemplate::with('cashbox')->whereIn('announce_template_id', $request->input('ids'))->get();

        return view('prints.announce_print', ['templates' => $templates]);
    }

    /**
     * Update the payer name for a single announce template.
     */
    public function updatePayerName(Request $request, AnnounceTemplate $announceTemplate)
    {
        $request->validate(['payer_name' => 'required|string|max:255']);

        $announceTemplate->update([
            'payer_name' => $request->input('payer_name'),
        ]);

        return redirect()->back()->with('success', 'Payer name updated successfully.');
    }

    /**
     * Batch update the payer name for multiple announce templates.
     */
    public function batchUpdatePayerName(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:announce_templates,announce_template_id',
            'payer_name' => 'required|string|max:255',
        ]);

        AnnounceTemplate::whereIn('announce_template_id', $request->input('ids'))
            ->update(['payer_name' => $request->input('payer_name')]);

        return redirect()->back()->with('success', 'Payer names updated successfully.');
    }
}

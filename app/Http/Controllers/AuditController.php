<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = Audit::with('user')->whereNotNull('user_id');

        // Filtrar por tipo
        if ($request->has('type')) {
            $query->where('auditable_type', 'LIKE', '%' . $request->type . '%');
        }

        // Filtrar por acción
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        // Filtrar por usuario
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filtrar por fecha
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $audits = $query->orderBy('created_at', 'desc')
                        ->get();

        return view('admin.audits.index', compact('audits'));
    }

    /**
     * Muestra un registro específico.
     */
    public function show($id)
    {
        $audit = Audit::findOrFail($id);
        return view('admin.audits.show', compact('audit'));
    }

    /**
     * Muestra la historia de auditoría para un modelo específico.
     */
    public function history(Request $request, $type, $id)
    {
        $className = 'App\\Models\\' . ucfirst($type);

        if (!class_exists($className)) {
            return back()->with('error', 'Tipo de modelo no encontrado');
        }

        $audits = Audit::where('auditable_type', $className)
                        ->where('auditable_id', $id)
                        ->orderBy('created_at', 'desc')
                        ->paginate(20);

        $item = $className::find($id);

        return view('admin.audits.history', compact('audits', 'item', 'type'));
    }
}

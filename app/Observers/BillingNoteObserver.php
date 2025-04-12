<?php

namespace App\Observers;

use App\Models\Audit;
use App\Models\BillingNote;
use Illuminate\Support\Facades\Auth;

class BillingNoteObserver
{
    public function created(BillingNote $billingNote)
    {
        $this->auditBillingNote($billingNote, 'created');
        $this->auditItems($billingNote, 'created');
    }

    public function updated(BillingNote $billingNote)
    {
        $this->auditBillingNote($billingNote, 'updated');
        if ($billingNote->isDirty('total_amount') || $billingNote->wasChanged()) {
            $this->auditItems($billingNote, 'updated');
        }
    }

    public function deleted(BillingNote $billingNote)
    {
        $this->auditBillingNote($billingNote, 'deleted');
        $this->auditItems($billingNote, 'deleted');
    }

    protected function auditBillingNote(BillingNote $billingNote, string $action)
    {
        $oldValues = $newValues = [];

        if ($action === 'updated') {
            $oldValues = $billingNote->getOriginal();
            $newValues = $billingNote->getChanges();
        } elseif ($action === 'created') {
            $newValues = $billingNote->getAttributes();
        } elseif ($action === 'deleted') {
            $oldValues = $billingNote->getAttributes();
        }
        unset($oldValues['created_at'], $oldValues['updated_at']);
        unset($newValues['created_at'], $newValues['updated_at']);

        Audit::create([
            'auditable_type' => get_class($billingNote),
            'auditable_id' => $billingNote->id,
            'action' => $action,
            'user_id' => Auth::id(),
            'old_values' => $oldValues,
            'new_values' => $newValues
        ]);
    }

    protected function auditItems(BillingNote $billingNote, string $action)
    {
        // Cargar items si no están cargados
        if (!$billingNote->relationLoaded('items')) {
            $billingNote->load('items');
        }

        $itemsData = $billingNote->items->map(function ($item) use ($billingNote) {
            return [
                'id' => $item->id,
                'cost_id' => $item->cost_id,
                'description' => $item->description,
                'amount' => $item->amount,
                'currency' => $item->currency,
                'billing_note_id' => $billingNote->id, // Referencia directa
                'billing_note_number' => $billingNote->note_number // Identificador legible
            ];
        })->toArray();

        Audit::create([
            'auditable_type' => get_class($billingNote),
            'auditable_id' => $billingNote->id,
            'action' => $action . '_items',
            'user_id' => Auth::id(),
            'old_values' => $action === 'updated' ? $this->getPreviousItems($billingNote) : [],
            'new_values' => ['items' => $itemsData]
        ]);
    }

    protected function getPreviousItems(BillingNote $billingNote)
    {
        $originalId = $billingNote->getOriginal('id');
        if (!$originalId) return [];

        $previous = BillingNote::with('items')->find($originalId);

        return $previous->items->map(function ($item) use ($previous){
            return [
                'id' => $item->id,
                'cost_id' => $item->cost_id,
                'description' => $item->description,
                'amount' => $item->amount,
                'currency' => $item->currency,
                'billing_note_id' => $previous->id,
                'billing_note_number' => $previous->note_number
            ];
        })->toArray();
    }
}

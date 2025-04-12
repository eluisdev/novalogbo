<?php

namespace App\Observers;

use App\Models\Audit;
use App\Models\Quotation;
use Illuminate\Support\Facades\Auth;

class QuotationObserver
{
    public function created(Quotation $quotation)
    {
        $this->logMainAudit('created', $quotation);
        $this->logRelatedData($quotation, 'created');
    }

    public function updated(Quotation $quotation)
    {
        $this->logMainAudit('updated', $quotation);
        $this->logRelatedData($quotation, 'updated');
    }

    public function deleted(Quotation $quotation)
    {
        $this->logMainAudit('deleted', $quotation);
        $this->logRelatedData($quotation, 'deleted');
    }

    protected function logMainAudit($action, Quotation $quotation)
    {
        $oldValues = $newValues = [];

        if ($action === 'updated') {
            $oldValues = $quotation->getOriginal();
            $newValues = $quotation->getChanges();
        } elseif ($action === 'created') {
            $newValues = $quotation->getAttributes();
        } elseif ($action === 'deleted') {
            $oldValues = $quotation->getAttributes();
        }

        Audit::create([
            'auditable_type' => get_class($quotation),
            'auditable_id' => $quotation->id,
            'action' => $action,
            'user_id' => Auth::id(),
            'old_values' => $oldValues,
            'new_values' => $newValues
        ]);
    }

    protected function logRelatedData(Quotation $quotation, $action)
    {
        // Auditoría agrupada para servicios
        $this->logGroupedRelation(
            $quotation,
            'services',
            ['service_id', 'included'],
            $action . '_services'
        );

        // Auditoría agrupada para costos
        $this->logGroupedRelation(
            $quotation,
            'costDetails',
            ['cost_id', 'amount', 'concept', 'currency'],
            $action . '_costs'
        );
    }


    protected function logGroupedRelation($quotation, $relation, $fields, $actionType)
    {
        $current = $quotation->$relation->map(function ($item) use ($fields, $quotation) {
            return array_merge(
                $item->only($fields),
                [
                    'quotation_id' => $quotation->id, // Referencia a la cotización
                ]
            );
        })->toArray();

        $previous = [];

        if ($actionType === 'updated_services' || $actionType === 'updated_costs') {
            $originalId = $quotation->getOriginal('id');
            if ($originalId) {
                $previous = Quotation::with($relation)
                    ->find($originalId)
                    ->$relation
                    ->map(function ($item) use ($fields, $quotation) {
                        return array_merge(
                            $item->only($fields),
                            [
                                'quotation_id' => $quotation->id,
                            ]
                        );
                    })
                    ->toArray();
            }
        }

        if ($current !== $previous) {
            Audit::create([
                'auditable_type' => get_class($quotation),
                'auditable_id' => $quotation->id,
                'action' => $actionType,
                'user_id' => Auth::id(),
                'old_values' => json_encode($previous),
                'new_values' => json_encode($current)
            ]);
        }
    }
}

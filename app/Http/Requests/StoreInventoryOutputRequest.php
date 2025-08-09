<?php

namespace App\Http\Requests;

use App\Models\InventoryEntry;
use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryOutputRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'output_date' => 'required|date',
            'comment' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.inventory_entry_id' => 'required|exists:inventory_entries,id',
            'items.*.quantity' => [
                'required',
                'numeric',
                'min:0.01',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $entryId = $this->input('items')[$index]['inventory_entry_id'];
                    if ($entryId) {
                        $entry = InventoryEntry::find($entryId);
                        if ($entry && $value > $entry->quantity) {
                            $fail("The quantity for {$entry->product_name} exceeds the available stock of {$entry->quantity}.");
                        }
                    }
                },
            ],
            'items.*.price' => 'required|numeric|min:0',
        ];
    }
}

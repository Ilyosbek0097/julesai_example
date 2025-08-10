<?php

namespace App\Http\Requests;

use App\Models\OutputDetail;
use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'returns' => 'required|array|min:1',
            'returns.*.output_detail_id' => 'required|exists:output_details,id',
            'returns.*.quantity' => [
                'required',
                'numeric',
                'min:0.01',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $outputDetailId = $this->input('returns')[$index]['output_detail_id'];
                    if ($outputDetailId) {
                        $detail = OutputDetail::find($outputDetailId);
                        if ($detail) {
                            $remainingReturnable = $detail->quantity - $detail->returned_quantity;
                            if ($value > $remainingReturnable) {
                                $fail("The return quantity of {$value} exceeds the remaining returnable quantity of {$remainingReturnable}.");
                            }
                        }
                    }
                },
            ],
            'comment' => 'nullable|string|max:1000',
        ];
    }
}

<?php

namespace App\Http\Requests\Branch;

use App\Models\StockTransfer;
use Illuminate\Foundation\Http\FormRequest;

class CompleteTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isBranchManager() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'return_reason' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'distinct'],
            'items.*.received_quantity' => ['required', 'integer', 'min:0'],
            'items.*.returned_quantity' => ['required', 'integer', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $transfer = $this->route('transfer');

            if (! $transfer instanceof StockTransfer) {
                return;
            }

            $transfer->loadMissing('items');
            $posted = collect($this->input('items', []))
                ->filter(fn ($row) => is_array($row))
                ->keyBy(fn (array $row) => (int) ($row['id'] ?? 0));

            if ($posted->count() !== $transfer->items->count()) {
                $validator->errors()->add('items', 'Enter an accepted quantity for every line.');
            }

            $returnedTotal = 0;

            foreach ($transfer->items as $item) {
                $row = $posted->get($item->id);

                if (! is_array($row)) {
                    $validator->errors()->add('items', 'Enter an accepted quantity for every line.');

                    continue;
                }

                $received = (int) $row['received_quantity'];
                $returned = (int) $row['returned_quantity'];

                if ($received + $returned !== $item->quantity) {
                    $validator->errors()->add(
                        'items.'.$item->id,
                        'Accepted and returned quantities must add up to the dispatched quantity.',
                    );
                }

                $returnedTotal += $returned;
            }

            if ($returnedTotal > 0 && ! filled($this->input('return_reason'))) {
                $validator->errors()->add('return_reason', 'A return reason is required when any quantity is returned.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required' => 'Enter an accepted quantity for every line.',
            'items.*.received_quantity.min' => 'Accepted quantity cannot be negative.',
            'items.*.returned_quantity.min' => 'Returned quantity cannot be negative.',
        ];
    }

    /**
     * @return list<array{id: int, received_quantity: int, returned_quantity: int}>
     */
    public function lines(): array
    {
        return collect($this->validated('items'))
            ->map(fn (array $item) => [
                'id' => (int) $item['id'],
                'received_quantity' => (int) $item['received_quantity'],
                'returned_quantity' => (int) $item['returned_quantity'],
            ])
            ->values()
            ->all();
    }

    public function reason(): ?string
    {
        $reason = $this->validated('return_reason');

        return filled($reason) ? $reason : null;
    }
}

<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

trait UpdatesActiveStatus
{
    protected function updateActiveStatus(Request $request, Model $record, string $label): RedirectResponse
    {
        $status = $request->validate([
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ])['status'];

        $record->update(['status' => $status]);

        $verb = $status === 'active' ? 'activated' : 'deactivated';

        return back()->with('success', $label.' '.$verb.'.');
    }
}

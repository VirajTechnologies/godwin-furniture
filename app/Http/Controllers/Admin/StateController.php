<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\UpdatesActiveStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StateRequest;
use App\Models\State;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StateController extends Controller
{
    use UpdatesActiveStatus;

    public function index(): View
    {
        return view('admin.states.index', [
            'states' => State::query()->orderBy('state_name')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.states.create', [
            'state' => new State(['status' => State::STATUS_ACTIVE]),
        ]);
    }

    public function store(StateRequest $request): RedirectResponse
    {
        State::query()->create($request->validated());

        return redirect()->route('admin.states.index')->with('success', 'State saved.');
    }

    public function edit(State $state): View
    {
        return view('admin.states.edit', [
            'state' => $state,
        ]);
    }

    public function update(StateRequest $request, State $state): RedirectResponse
    {
        $state->update($request->validated());

        return redirect()->route('admin.states.index')->with('success', 'State updated.');
    }

    public function updateStatus(Request $request, State $state): RedirectResponse
    {
        return $this->updateActiveStatus($request, $state, $state->state_name);
    }
}

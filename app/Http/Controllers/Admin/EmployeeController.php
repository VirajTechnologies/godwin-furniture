<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EmployeeRequest;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(): View
    {
        $employees = Employee::query()
            ->with(['user.role', 'warehouse', 'branch'])
            ->orderBy('employee_code')
            ->paginate(15);

        return view('admin.employees.index', [
            'employees' => $employees,
        ]);
    }

    public function create(): View
    {
        return view('admin.employees.create', [
            'employee' => new Employee(['status' => Employee::STATUS_ACTIVE]),
            'roles' => $this->roles(),
            'warehouses' => $this->warehouses(),
            'branches' => $this->branches(),
        ]);
    }

    public function store(EmployeeRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $user = User::query()->create([
                'name' => $request->string('name')->toString(),
                'email' => $request->string('email')->toString(),
                'phone' => $request->input('phone'),
                'password' => $request->string('password')->toString(),
                'role_id' => $request->integer('role_id'),
                'status' => $request->string('status')->toString(),
            ]);

            $user->employee()->create([
                'employee_code' => $request->string('employee_code')->toString(),
                'warehouse_id' => $request->input('warehouse_id'),
                'branch_id' => $request->input('branch_id'),
                'designation' => $request->input('designation'),
                'status' => $request->string('status')->toString(),
            ]);
        });

        return redirect()->route('admin.employees.index')->with('success', 'Employee saved.');
    }

    public function edit(Employee $employee): View
    {
        $employee->load('user');

        return view('admin.employees.edit', [
            'employee' => $employee,
            'roles' => $this->roles((int) old('role_id', $employee->user->role_id)),
            'warehouses' => $this->warehouses((int) old('warehouse_id', $employee->warehouse_id) ?: null),
            'branches' => $this->branches((int) old('branch_id', $employee->branch_id) ?: null),
        ]);
    }

    public function update(EmployeeRequest $request, Employee $employee): RedirectResponse
    {
        DB::transaction(function () use ($request, $employee): void {
            $userData = [
                'name' => $request->string('name')->toString(),
                'email' => $request->string('email')->toString(),
                'phone' => $request->input('phone'),
                'role_id' => $request->integer('role_id'),
                'status' => $request->string('status')->toString(),
            ];

            if ($request->filled('password')) {
                $userData['password'] = $request->string('password')->toString();
            }

            $employee->user()->update($userData);
            $employee->update([
                'warehouse_id' => $request->input('warehouse_id'),
                'branch_id' => $request->input('branch_id'),
                'designation' => $request->input('designation'),
                'status' => $request->string('status')->toString(),
            ]);
        });

        return redirect()->route('admin.employees.index')->with('success', 'Employee updated.');
    }

    public function updateStatus(Request $request, Employee $employee): RedirectResponse
    {
        if ($employee->user_id === $request->user()?->id && $request->input('status') === Employee::STATUS_INACTIVE) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $status = $request->validate([
            'status' => ['required', 'in:active,inactive'],
        ])['status'];

        $employee->update(['status' => $status]);
        $employee->user()->update(['status' => $status]);

        $verb = $status === Employee::STATUS_ACTIVE ? 'activated' : 'deactivated';

        return back()->with('success', $employee->user->name.' '.$verb.'.');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Role>
     */
    private function roles(?int $selectedId = null)
    {
        return Role::query()
            ->where(function ($query) use ($selectedId): void {
                $query->where('status', Role::STATUS_ACTIVE);

                if ($selectedId) {
                    $query->orWhere('id', $selectedId);
                }
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Warehouse>
     */
    private function warehouses(?int $selectedId = null)
    {
        return Warehouse::query()
            ->where(function ($query) use ($selectedId): void {
                $query->where('status', Warehouse::STATUS_ACTIVE);

                if ($selectedId) {
                    $query->orWhere('id', $selectedId);
                }
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Branch>
     */
    private function branches(?int $selectedId = null)
    {
        return Branch::query()
            ->where(function ($query) use ($selectedId): void {
                $query->where('status', Branch::STATUS_ACTIVE);

                if ($selectedId) {
                    $query->orWhere('id', $selectedId);
                }
            })
            ->orderBy('name')
            ->get();
    }
}

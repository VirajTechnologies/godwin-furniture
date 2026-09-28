<?php

namespace App\Http\Requests\Admin;

use App\Models\Employee;
use App\Models\Role;
use App\Models\Warehouse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $employee = $this->route('employee');
        $userId = $employee instanceof Employee ? $employee->user_id : null;

        $rules = [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => [$employee instanceof Employee ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')->where('status', Role::STATUS_ACTIVE)],
            'warehouse_id' => ['nullable', 'integer', Rule::exists('warehouses', 'id')],
            'designation' => ['nullable', 'string', 'max:150'],
            'status' => ['required', Rule::in([Employee::STATUS_ACTIVE, Employee::STATUS_INACTIVE])],
        ];

        if (! $employee instanceof Employee) {
            $rules['employee_code'] = ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9-]+$/', Rule::unique('employee_profiles', 'employee_code')];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('employee_code')) {
            $this->merge([
                'employee_code' => strtoupper((string) $this->input('employee_code')),
            ]);
        }

        if ($this->input('warehouse_id') === '') {
            $this->merge(['warehouse_id' => null]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $role = Role::query()->find($this->integer('role_id'));
            $warehouse = Warehouse::query()->find($this->integer('warehouse_id'));
            $employee = $this->route('employee');
            $currentWarehouseId = $employee instanceof Employee ? $employee->warehouse_id : null;

            if ($role?->slug === Role::WAREHOUSE_STAFF && ! $warehouse) {
                $validator->errors()->add('warehouse_id', 'Choose the warehouse this employee works in.');
            }

            if ($warehouse && ! $warehouse->isActive() && $warehouse->id !== $currentWarehouseId) {
                $validator->errors()->add('warehouse_id', 'Choose an active warehouse.');
            }

            if ($role && $role->slug !== Role::WAREHOUSE_STAFF && $this->filled('warehouse_id')) {
                $validator->errors()->add('warehouse_id', 'A warehouse is only set for warehouse staff.');
            }

            if ($employee instanceof Employee
                && $this->input('status') === Employee::STATUS_INACTIVE
                && $employee->user_id === $this->user()?->id) {
                $validator->errors()->add('status', 'You cannot deactivate your own account.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'employee_code.regex' => 'The employee code may contain only letters, numbers, and hyphens.',
        ];
    }
}

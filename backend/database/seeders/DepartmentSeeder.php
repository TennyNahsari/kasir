<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        $departments = [
            [
                'code' => 'SALES',
                'name' => 'Sales',
                'manager_name' => 'John Doe',
                'phone' => '081234567890',
                'email' => 'sales@example.com',
                'cost_center' => 'CC-SALES-001',
                'budget_limit' => 50000000,
                'is_active' => true,
                'description' => 'Sales and Marketing Department'
            ],
            [
                'code' => 'OPS',
                'name' => 'Operations',
                'manager_name' => 'Jane Smith',
                'phone' => '081234567891',
                'email' => 'ops@example.com',
                'cost_center' => 'CC-OPS-001',
                'budget_limit' => 100000000,
                'is_active' => true,
                'description' => 'Operations and Production Department'
            ],
            [
                'code' => 'IT',
                'name' => 'IT',
                'manager_name' => 'Bob Johnson',
                'phone' => '081234567892',
                'email' => 'it@example.com',
                'cost_center' => 'CC-IT-001',
                'budget_limit' => 75000000,
                'is_active' => true,
                'description' => 'Information Technology Department'
            ],
            [
                'code' => 'FIN',
                'name' => 'Finance',
                'manager_name' => 'Alice Brown',
                'phone' => '081234567893',
                'email' => 'finance@example.com',
                'cost_center' => 'CC-FIN-001',
                'budget_limit' => 30000000,
                'is_active' => true,
                'description' => 'Finance and Accounting Department'
            ],
            [
                'code' => 'HR',
                'name' => 'Human Resources',
                'manager_name' => 'Charlie Davis',
                'phone' => '081234567894',
                'email' => 'hr@example.com',
                'cost_center' => 'CC-HR-001',
                'budget_limit' => 40000000,
                'is_active' => true,
                'description' => 'Human Resources Department'
            ],
            [
                'code' => 'ADMIN',
                'name' => 'Administration',
                'manager_name' => 'Diana Wilson',
                'phone' => '081234567895',
                'email' => 'admin@example.com',
                'cost_center' => 'CC-ADMIN-001',
                'budget_limit' => 25000000,
                'is_active' => true,
                'description' => 'General Administration Department'
            ],
        ];

        foreach ($departments as $department) {
            Department::firstOrCreate(['code' => $department['code']], $department);
        }
    }
}

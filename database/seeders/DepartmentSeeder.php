<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the example IT department
        Department::create([
            'department_name' => 'Information Technology',
            'department_code' => 'IT',
            'description' => 'Handles system development and IT support.',
            'manager_id' => null, // Can be set later when employee ID 23 exists
            'status' => 'Active',
        ]);

        // You can add more departments here if needed
        Department::create([
            'department_name' => 'Human Resources',
            'department_code' => 'HR',
            'description' => 'Manages employee relations, recruitment, and company policies.',
            'manager_id' => null, // Can be set later
            'status' => 'Active',
        ]);

        Department::create([
            'department_name' => 'Finance',
            'department_code' => 'FIN',
            'description' => 'Handles financial planning, accounting, and budget management.',
            'manager_id' => null,
            'status' => 'Active',
        ]);
    }
}

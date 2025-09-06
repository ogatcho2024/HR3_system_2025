<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Reimbursement;
use App\Models\User;
use App\Models\Employee;
use Carbon\Carbon;

class ReimbursementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some users to assign reimbursements to
        $users = User::limit(5)->get();
        
        if ($users->count() === 0) {
            $this->command->info('No users found. Please create some users first.');
            return;
        }
        
        $reimbursementTypes = ['Travel', 'Meal', 'Office Supplies', 'Training', 'Equipment', 'Medical', 'Communication'];
        $statuses = ['pending', 'approved', 'rejected'];
        
        foreach ($users as $user) {
            // Create 3-5 reimbursements per user
            $count = rand(3, 5);
            
            for ($i = 0; $i < $count; $i++) {
                $submittedDate = Carbon::now()->subDays(rand(1, 90));
                $status = $statuses[array_rand($statuses)];
                
                $reimbursement = [
                    'user_id' => $user->id,
                    'employee_id' => $user->employee?->id,
                    'type' => $reimbursementTypes[array_rand($reimbursementTypes)],
                    'amount' => round(rand(25, 500) + (rand(0, 99) / 100), 2),
                    'description' => $this->getDescription(),
                    'status' => $status,
                    'submitted_date' => $submittedDate,
                    'created_at' => $submittedDate,
                    'updated_at' => $submittedDate,
                ];
                
                // Add approval/rejection data if not pending
                if ($status !== 'pending') {
                    $reimbursement['approved_date'] = $submittedDate->copy()->addDays(rand(1, 7));
                    $reimbursement['approved_by'] = $users->random()->id; // Random approver
                    
                    if ($status === 'rejected') {
                        $reimbursement['rejection_reason'] = $this->getRejectionReason();
                    }
                }
                
                Reimbursement::create($reimbursement);
            }
        }
        
        $this->command->info('Reimbursement data seeded successfully!');
    }
    
    /**
     * Get random description for reimbursement
     */
    private function getDescription(): string
    {
        $descriptions = [
            'Client meeting transportation costs',
            'Business lunch with potential client',
            'Office supplies for project work',
            'Professional development course fee',
            'New laptop for remote work',
            'Prescription glasses for work',
            'Mobile phone bill for business use',
            'Conference attendance and materials',
            'Team building event expenses',
            'Emergency taxi fare after late meeting',
            'Printing costs for client presentation',
            'Software license for project tools'
        ];
        
        return $descriptions[array_rand($descriptions)];
    }
    
    /**
     * Get random rejection reason
     */
    private function getRejectionReason(): string
    {
        $reasons = [
            'Receipt not provided or unclear',
            'Exceeds monthly reimbursement limit',
            'Not a valid business expense',
            'Duplicate submission detected',
            'Missing manager approval',
            'Outside of reimbursement policy guidelines'
        ];
        
        return $reasons[array_rand($reasons)];
    }
}

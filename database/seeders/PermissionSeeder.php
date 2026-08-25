<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // 1. Organization Group (Only Manager & Admin; strictly forbidden for Engineer)
            [
                'id' => 'company.create',
                'name' => 'Create Company',
                'group_name' => 'organization',
                'allowed_roles' => ['manager'],
                'description' => 'Allow creating a new company in the system',
            ],
            [
                'id' => 'company.update',
                'name' => 'Update Company',
                'group_name' => 'organization',
                'allowed_roles' => ['manager'],
                'description' => 'Allow updating company details',
            ],
            [
                'id' => 'company.delete',
                'name' => 'Delete Company',
                'group_name' => 'organization',
                'allowed_roles' => ['manager'],
                'description' => 'Allow deleting a company from the system',
            ],
            [
                'id' => 'department.create',
                'name' => 'Create Department',
                'group_name' => 'organization',
                'allowed_roles' => ['manager'],
                'description' => 'Allow creating a new department',
            ],
            [
                'id' => 'department.update',
                'name' => 'Update Department',
                'group_name' => 'organization',
                'allowed_roles' => ['manager'],
                'description' => 'Allow updating department details',
            ],
            [
                'id' => 'department.delete',
                'name' => 'Delete Department',
                'group_name' => 'organization',
                'allowed_roles' => ['manager'],
                'description' => 'Allow deleting a department',
            ],
            [
                'id' => 'user.create',
                'name' => 'Create User',
                'group_name' => 'organization',
                'allowed_roles' => ['manager'],
                'description' => 'Allow creating a new user account',
            ],
            [
                'id' => 'user.update',
                'name' => 'Update User',
                'group_name' => 'organization',
                'allowed_roles' => ['manager'],
                'description' => 'Allow updating other user accounts',
            ],
            [
                'id' => 'user.delete',
                'name' => 'Delete User',
                'group_name' => 'organization',
                'allowed_roles' => ['manager'],
                'description' => 'Allow deleting user accounts from the system',
            ],

            // 2. Equipment Masterdata Group (Manager & Engineer)
            [
                'id' => 'equipment.create',
                'name' => 'Create Equipment',
                'group_name' => 'equipment_masterdata',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Allow creating new equipment in Masterdata',
            ],
            [
                'id' => 'equipment.update',
                'name' => 'Update Equipment',
                'group_name' => 'equipment_masterdata',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Allow editing equipment details',
            ],
            [
                'id' => 'equipment.delete',
                'name' => 'Delete Equipment',
                'group_name' => 'equipment_masterdata',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Allow deleting equipment',
            ],
            [
                'id' => 'equipment.mark_maintenance',
                'name' => 'Mark Last Maintenance',
                'group_name' => 'equipment_masterdata',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Allow marking the latest maintenance date for equipment',
            ],
            [
                'id' => 'equipment.update_parent',
                'name' => 'Update Parent Equipment',
                'group_name' => 'equipment_masterdata',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Allow updating parent-child equipment hierarchy',
            ],
            [
                'id' => 'equipment.update_errors',
                'name' => 'Update Equipment Errors',
                'group_name' => 'equipment_masterdata',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Allow assigning or unassigning errors to equipment',
            ],
            [
                'id' => 'equipment_category.manage',
                'name' => 'Manage Equipment Categories',
                'group_name' => 'equipment_masterdata',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Create, update, and delete equipment categories',
            ],
            [
                'id' => 'equipment_parameter.manage',
                'name' => 'Manage Equipment Parameters',
                'group_name' => 'equipment_masterdata',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Create, update, and delete equipment parameters',
            ],
            [
                'id' => 'equipment_error.manage',
                'name' => 'Manage Equipment Error Masterdata',
                'group_name' => 'equipment_masterdata',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Create, update, and delete equipment error types',
            ],
            [
                'id' => 'equipment_state.manage',
                'name' => 'Manage Equipment States',
                'group_name' => 'equipment_masterdata',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Create, update, and delete equipment operating states',
            ],
            [
                'id' => 'unit.manage',
                'name' => 'Manage Measurement Units',
                'group_name' => 'equipment_masterdata',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Create, update, and delete measurement units',
            ],

            // 3. Checklist Operations Group (Manager & Engineer)
            [
                'id' => 'checklist_session.create',
                'name' => 'Create Checklist Session',
                'group_name' => 'checklist',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Allow creating a new checklist session',
            ],
            [
                'id' => 'checklist_session.update',
                'name' => 'Update Checklist Session',
                'group_name' => 'checklist',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Allow updating checklist session details',
            ],
            [
                'id' => 'checklist_session.delete',
                'name' => 'Delete Checklist Session',
                'group_name' => 'checklist',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Allow deleting a checklist session',
            ],
            [
                'id' => 'checklist_session.judge',
                'name' => 'Judge Checklist Session',
                'group_name' => 'checklist',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Allow evaluating and submitting checklist session results',
            ],
            [
                'id' => 'checklist_detail.manage',
                'name' => 'Manage Checklist Details',
                'group_name' => 'checklist',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Create, update, and delete checklist item details',
            ],
            [
                'id' => 'checklist_schedule.complete',
                'name' => 'Complete Checklist Schedule',
                'group_name' => 'checklist',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Mark a checklist schedule as completed',
            ],
            [
                'id' => 'checklist_schedule.delete_daily',
                'name' => 'Delete Daily Checklist Schedules',
                'group_name' => 'checklist',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Delete auto-generated daily checklist schedules',
            ],

            // 4. Maintenance Operations Group (Manager & Engineer)
            [
                'id' => 'maintenance_plan.create',
                'name' => 'Create Maintenance Plan',
                'group_name' => 'maintenance',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Allow creating a new maintenance plan',
            ],
            [
                'id' => 'maintenance_plan.update',
                'name' => 'Update Maintenance Plan',
                'group_name' => 'maintenance',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Allow updating a maintenance plan',
            ],
            [
                'id' => 'maintenance_plan.delete',
                'name' => 'Delete Maintenance Plan',
                'group_name' => 'maintenance',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Allow deleting a maintenance plan',
            ],
            [
                'id' => 'maintenance_plan.judge',
                'name' => 'Judge Maintenance Plan',
                'group_name' => 'maintenance',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Allow approving and judging maintenance plan execution',
            ],
            [
                'id' => 'maintenance_schedule.update',
                'name' => 'Update Maintenance Schedule',
                'group_name' => 'maintenance',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Allow updating scheduled maintenance dates and assignments',
            ],
            [
                'id' => 'maintenance_schedule.complete',
                'name' => 'Complete Maintenance Schedule',
                'group_name' => 'maintenance',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Mark scheduled maintenance as completed',
            ],
            [
                'id' => 'maintenance_schedule.delete',
                'name' => 'Delete Maintenance Schedule',
                'group_name' => 'maintenance',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Allow deleting scheduled maintenance items',
            ],
            [
                'id' => 'maintenance_log.create',
                'name' => 'Create Maintenance Log',
                'group_name' => 'maintenance',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Allow logging new maintenance activities',
            ],
            [
                'id' => 'maintenance_log.update',
                'name' => 'Update Maintenance Log',
                'group_name' => 'maintenance',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Allow updating maintenance log entries',
            ],
            [
                'id' => 'maintenance_log.delete',
                'name' => 'Delete Maintenance Log',
                'group_name' => 'maintenance',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Allow deleting maintenance log entries',
            ],
            [
                'id' => 'maintenance_category.manage',
                'name' => 'Manage Maintenance Categories',
                'group_name' => 'maintenance',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Create, update, and delete maintenance categories',
            ],
            [
                'id' => 'maintenance_item.manage',
                'name' => 'Manage Maintenance Items',
                'group_name' => 'maintenance',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Create, update, and delete maintenance item definitions',
            ],

            // 5. Monitoring & Logs Group (Manager & Engineer)
            [
                'id' => 'equipment_error_log.create',
                'name' => 'Create Equipment Error Log',
                'group_name' => 'monitoring_logs',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Record incident and breakdown logs for equipment',
            ],
            [
                'id' => 'equipment_error_log.update',
                'name' => 'Update Equipment Error Log',
                'group_name' => 'monitoring_logs',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Update resolution status and details for equipment error logs',
            ],
            [
                'id' => 'equipment_error_log.delete',
                'name' => 'Delete Equipment Error Log',
                'group_name' => 'monitoring_logs',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Delete equipment error log records',
            ],
            [
                'id' => 'operating_time.create',
                'name' => 'Create Operating Time',
                'group_name' => 'monitoring_logs',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Record machine operating runtime hours',
            ],
            [
                'id' => 'operating_time.update',
                'name' => 'Update Operating Time',
                'group_name' => 'monitoring_logs',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Update operating runtime entries',
            ],
            [
                'id' => 'operating_time.delete',
                'name' => 'Delete Operating Time',
                'group_name' => 'monitoring_logs',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Delete operating runtime entries',
            ],
            [
                'id' => 'operating_time.import',
                'name' => 'Import Operating Time',
                'group_name' => 'monitoring_logs',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Import machine operating runtime from Excel/CSV',
            ],
            [
                'id' => 'equipment_parameter_log.create',
                'name' => 'Create Equipment Parameter Log',
                'group_name' => 'monitoring_logs',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Record measured parameter values for equipment',
            ],
            [
                'id' => 'equipment_parameter_log.update',
                'name' => 'Update Equipment Parameter Log',
                'group_name' => 'monitoring_logs',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Update recorded parameter log entries',
            ],
            [
                'id' => 'equipment_parameter_log.delete',
                'name' => 'Delete Equipment Parameter Log',
                'group_name' => 'monitoring_logs',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Delete recorded parameter log entries',
            ],
            [
                'id' => 'equipment_parameter_log.import',
                'name' => 'Import Equipment Parameter Logs',
                'group_name' => 'monitoring_logs',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Import parameter measurement logs from Excel/CSV',
            ],
            [
                'id' => 'equipment_parameter_log.save',
                'name' => 'Save Batch Parameter Logs',
                'group_name' => 'monitoring_logs',
                'allowed_roles' => ['manager', 'engineer'],
                'description' => 'Save multiple parameter logs from the quick input screen',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['id' => $permission['id']],
                $permission
            );
        }
    }
}

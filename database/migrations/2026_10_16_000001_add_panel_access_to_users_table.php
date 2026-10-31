<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Panel access used to be inferred from role alone (see the old
     * canAccessPanel() match in App\Models\User). That coupled "what panel
     * can you enter" to "what's your job title" - fine until someone needed
     * an exception (e.g. a Sales user who shouldn't touch the admin panel).
     * These columns make panel access an explicit, per-user switch instead,
     * backfilled here from the role combinations that used to apply so
     * existing users keep exactly the access they have today.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('can_access_admin')->default(false)->after('is_active');
            $table->boolean('can_access_pos')->default(false)->after('can_access_admin');
            $table->boolean('can_access_service')->default(false)->after('can_access_pos');

            // When on, logging into the admin panel also authenticates this
            // user on the POS panel's guard in the background - lets staff
            // who regularly do both skip a second login, while everyone
            // else still gets POS's own separate login screen.
            $table->boolean('pos_shares_admin_login')->default(false)->after('can_access_service');
        });

        User::with('roles')->chunk(100, function ($users) {
            foreach ($users as $user) {
                $user->forceFill([
                    'can_access_admin' => $user->hasAnyRole(['Admin', 'Manager', 'Sales', 'Service Advisor']),
                    'can_access_pos' => $user->hasAnyRole(['Admin', 'Manager', 'Sales']),
                    'can_access_service' => $user->hasAnyRole(['Admin', 'Manager', 'Service Advisor']),
                ])->saveQuietly();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['can_access_admin', 'can_access_pos', 'can_access_service', 'pos_shares_admin_login']);
        });
    }
};

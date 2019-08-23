<?php

namespace Tests\Feature;

use App\Console\Commands\AdminInitCommand;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminInitCommandTest extends TestCase
{
    use RefreshDatabase;

    public function testInit()
    {
        $this->artisan('admin:init')
            ->expectsQuestion(AdminInitCommand::$initConfirmTip, false)
            ->assertExitCode(1);

        $this->artisan('admin:init')
            ->expectsQuestion(AdminInitCommand::$initConfirmTip, true)
            ->assertExitCode(0);

        $this->assertDatabaseHas('vue_routers', [
            'id' => 1,
            'path' => 'index',
            'title' => '首页',
        ]);
        $this->assertDatabaseHas('admin_users', [
            'username' => 'admin',
        ]);
        $this->assertDatabaseHas('admin_roles', [
            'slug' => 'administrator',
        ]);
        $this->assertDatabaseHas('admin_permissions', [
            'slug' => 'pass-all',
            'http_path' => '*',
        ]);
        $this->assertDatabaseHas('config_categories', [
            'id' => 1,
            'slug' => 'system_basic',
        ]);
        $this->assertDatabaseHas('configs', [
            'category_id' => 1,
            'slug' => 'app_name',
        ]);

        // 命令中用了 truncate，会导致回滚失败，所以手动清理一下
        $tables = [
            'vue_routers',
            'admin_users',
            'admin_roles',
            'admin_permissions',
            'config_categories',
            'configs',
            'admin_role_permission',
            'admin_user_permission',
            'admin_user_role',
            'vue_router_role',
        ];
        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }
    }
}

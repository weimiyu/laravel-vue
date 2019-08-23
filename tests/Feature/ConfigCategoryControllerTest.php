<?php

namespace Tests\Feature;

use App\Models\Config;
use App\Models\ConfigCategory;
use Tests\AdminTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\RequestActions;

class ConfigCategoryControllerTest extends AdminTestCase
{
    use RefreshDatabase;
    use RequestActions;
    protected $resourceName = 'config-categories';

    protected function setUp(): void
    {
        parent::setUp();
        $this->login();
    }

    public function testStoreValidation()
    {
        // slug 和 name 验证规则一样，验证一个即可

        // name required
        $res = $this->storeResource([
            'name' => '',
        ]);
        $res->assertStatus(422)
            ->assertJsonValidationErrors(['name']);

        // name string
        $res = $this->storeResource([
            'name' => [],
        ]);
        $res->assertStatus(422)
            ->assertJsonValidationErrors(['name']);

        // name max:50
        $res = $this->storeResource([
            'name' => str_repeat('a', 51),
        ]);
        $res->assertStatus(422)
            ->assertJsonValidationErrors(['name']);

        factory(ConfigCategory::class)->create(['name' => 'name']);
        // name unique
        $res = $this->storeResource([
            'name' => 'name',
        ]);
        $res->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function testStore()
    {
        $res = $this->storeResource([
            'name' => 'name',
            'slug' => 'slug',
        ]);
        $res->assertStatus(201);
        $this->assertDatabaseHas('config_categories', [
            'id' => $this->getLastInsertId('config_categories'),
            'name' => 'name',
            'slug' => 'slug',
        ]);
    }

    public function testUpdate()
    {
        $id = factory(ConfigCategory::class)->create(['name' => 'name'])->id;
        $res = $this->updateResource($id, [
            'name' => 'name',
        ]);
        $res->assertStatus(201);

        $res = $this->updateResource($id, [
            'name' => 'new',
            'slug' => 'new',
        ]);
        $res->assertStatus(201);
        $this->assertDatabaseHas('config_categories', [
            'id' => $id,
            'name' => 'new',
            'slug' => 'new',
        ]);
    }

    public function testDestroy()
    {
        $categoryId1 = factory(ConfigCategory::class)->create()->id;
        $categoryId2 = factory(ConfigCategory::class)->create()->id;
        $configId = factory(Config::class)->make(['category_id' => $categoryId2])->id;

        // 关联删除
        $res = $this->destroyResource($categoryId2);
        $res->assertStatus(204);
        $this->assertDatabaseMissing('config_categories', [
            'id' => $categoryId2,
        ]);
        $this->assertDatabaseMissing('configs', [
            'id' => $configId,
        ]);

        $res = $this->destroyResource($categoryId1);
        $res->assertStatus(204);
        $this->assertDatabaseMissing('config_categories', [
            'id' => $categoryId1,
        ]);
    }

    public function testIndex()
    {
        ConfigCategory::insert(factory(ConfigCategory::class, 20)->make()->toArray());
        ConfigCategory::first()->update(['name' => 'test query name']);
        ConfigCategory::offset(1)->first()->update(['name' => 'test query name 2']);

        // 查出所有
        $res = $this->getResources(['all' => 1]);
        $res->assertStatus(200)
            ->assertJsonCount(20);

        // name like %?%
        $res = $this->getResources([
            'name' => 'query',
        ]);
        $res->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }
}

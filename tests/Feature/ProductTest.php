<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create an admin user
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_create_product()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.products.store'), [
                'name' => 'Test Product',
                'description' => 'Test Description',
                'price' => 99.99,
                'category' => 'Electronics',
                'stock' => 100,
            ]);

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'price' => 99.99,
        ]);
    }

    public function test_admin_can_view_products()
    {
        Product::create([
            'name' => 'Test Product',
            'price' => 99.99,
            'stock' => 100,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.products.index'));

        $response->assertStatus(200);
        $response->assertSee('Test Product');
    }

    public function test_admin_can_update_product()
    {
        $product = Product::create([
            'name' => 'Original Name',
            'price' => 50.00,
            'stock' => 50,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->put(route('admin.products.update', $product), [
                'name' => 'Updated Name',
                'price' => 75.00,
                'stock' => 75,
            ]);

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Name',
            'price' => 75.00,
        ]);
    }

    public function test_admin_can_delete_product()
    {
        $product = Product::create([
            'name' => 'To Delete',
            'price' => 10.00,
            'stock' => 10,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.products.destroy', $product));

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}


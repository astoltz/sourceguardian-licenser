<?php

namespace Tests\Feature\Api\V1;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_all_customers()
    {
        Customer::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/customers');

        $response->assertOk();
        $response->assertJsonCount(3);
    }

    public function test_can_create_a_customer()
    {
        $data = [
            'display_name' => 'Test Customer',
        ];

        $response = $this->postJson('/api/v1/customers', $data);

        $response->assertCreated();
        $response->assertJsonFragment($data);
        $this->assertDatabaseHas('customers', $data);
    }

    public function test_can_get_a_customer()
    {
        $customer = Customer::factory()->create();

        $response = $this->getJson("/api/v1/customers/{$customer->id}");

        $response->assertOk();
        $response->assertJsonFragment(['id' => $customer->id]);
    }

    public function test_can_update_a_customer()
    {
        $customer = Customer::factory()->create();

        $data = [
            'display_name' => 'Updated Customer Name',
        ];

        $response = $this->putJson("/api/v1/customers/{$customer->id}", $data);

        $response->assertOk();
        $response->assertJsonFragment($data);
        $this->assertDatabaseHas('customers', $data);
    }

    public function test_can_sync_customer_constants()
    {
        $customer = Customer::factory()->create();
        $existingConstant = $customer->customerConstants()->create(['key' => 'existing', 'data' => 'data']);

        $data = [
            'customer_constants' => [
                ['id' => $existingConstant->id, 'key' => 'existing', 'data' => 'updated data'],
                ['key' => 'new', 'data' => 'new data'],
            ],
        ];

        $response = $this->putJson("/api/v1/customers/{$customer->id}", $data);

        $response->assertOk();
        $this->assertDatabaseHas('customer_constants', ['id' => $existingConstant->id, 'data' => 'updated data']);
        $this->assertDatabaseHas('customer_constants', ['key' => 'new', 'data' => 'new data']);
    }

    public function test_can_sync_customer_header_texts()
    {
        $customer = Customer::factory()->create();
        $existingText = $customer->customerHeaderTexts()->create(['data' => 'existing text']);

        $data = [
            'customer_header_texts' => [
                ['id' => $existingText->id, 'data' => 'updated text'],
                ['data' => 'new text'],
            ],
        ];

        $response = $this->putJson("/api/v1/customers/{$customer->id}", $data);

        $response->assertOk();
        $this->assertDatabaseHas('customer_header_texts', ['id' => $existingText->id, 'data' => 'updated text']);
        $this->assertDatabaseHas('customer_header_texts', ['data' => 'new text']);
    }

    public function test_can_delete_a_customer()
    {
        $customer = Customer::factory()->create();

        $response = $this->deleteJson("/api/v1/customers/{$customer->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }
}

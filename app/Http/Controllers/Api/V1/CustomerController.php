<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Traits\SyncsHasMany;
use Illuminate\Http\Request;

/**
 * API Controller for managing customers.
 */
class CustomerController extends Controller
{
    use SyncsHasMany;

    /**
     * Display a listing of customers.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index()
    {
        return Customer::with(['customerConstants', 'customerHeaderTexts'])->paginate(15);
    }

    /**
     * Search for customers by name or ID.
     *
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
        return Customer::where('display_name', 'like', "%{$query}%")
            ->orWhere('id', 'like', "%{$query}%")
            ->limit(10)
            ->get();
    }

    /**
     * Store a newly created customer in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'enabled' => 'boolean',
        ]);

        $customer = Customer::create($validated);

        return response()->json($customer, 201);
    }

    /**
     * Display the specified customer.
     *
     * @param Customer $customer
     * @return Customer
     */
    public function show(Customer $customer)
    {
        return $customer->load(['customerConstants', 'customerHeaderTexts']);
    }

    /**
     * Update the specified customer in storage.
     *
     * @param Request $request
     * @param Customer $customer
     * @return Customer
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'display_name' => 'sometimes|string|max:255',
            'enabled' => 'boolean',
            'customer_constants' => 'sometimes|array',
            'customer_header_texts' => 'sometimes|array',
        ]);

        $customer->update($validated);

        if (isset($validated['customer_constants'])) {
            $this->syncHasMany($customer->customerConstants(), $validated['customer_constants']);
        }

        if (isset($validated['customer_header_texts'])) {
            $this->syncHasMany($customer->customerHeaderTexts(), $validated['customer_header_texts']);
        }

        return $customer->load(['customerConstants', 'customerHeaderTexts']);
    }

    /**
     * Remove the specified customer from storage.
     *
     * @param Customer $customer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return response()->noContent();
    }
}

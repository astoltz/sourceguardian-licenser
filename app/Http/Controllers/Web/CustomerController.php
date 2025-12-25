<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Traits\SyncsHasMany;
use Illuminate\Http\Request;

/**
 * Controller for managing customers via the Web UI.
 */
class CustomerController extends Controller
{
    use SyncsHasMany;

    /**
     * Display a listing of customers.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $customers = Customer::paginate(15);
        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new customer.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created customer in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'enabled' => 'boolean',
            'customer_constants' => 'sometimes|array',
            'customer_header_texts' => 'sometimes|array',
        ]);

        $customer = Customer::create($validated);

        if (isset($validated['customer_constants'])) {
            $this->syncHasMany($customer->customerConstants(), $validated['customer_constants']);
        }
        if (isset($validated['customer_header_texts'])) {
            $this->syncHasMany($customer->customerHeaderTexts(), $validated['customer_header_texts']);
        }

        return redirect()->route('web.customers.show', $customer)->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified customer.
     *
     * @param Customer $customer
     * @return \Illuminate\View\View
     */
    public function show(Customer $customer)
    {
        $customer->load(['customerConstants', 'customerHeaderTexts']);
        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified customer.
     *
     * @param Customer $customer
     * @return \Illuminate\View\View
     */
    public function edit(Customer $customer)
    {
        $customer->load(['customerConstants', 'customerHeaderTexts']);
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer in storage.
     *
     * @param Request $request
     * @param Customer $customer
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
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

        return redirect()->route('web.customers.show', $customer)->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified customer from storage.
     *
     * @param Customer $customer
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Customer $customer)
    {
        try {
            $customer->delete();
            return redirect()->route('web.customers.index')->with('success', 'Customer deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

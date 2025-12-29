<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Validation\Rule;

class CustomersController extends Controller
{

        public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|max:255',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'type' => 'customer',
        ]);
        return redirect()->route('customers.index')->with('success', 'Customer added successfully');
    }

    public function create()
    {
        return view('customers.create');
    }

    // DISPLAY (one)
    public function displayCustomer(User $customer)
    {
        return view('customers.customer', compact('customer'));
    }

    // DISPLAY (many)
    public function index()
    {
        $customers = User::where('type', 'customer')->orderByDesc('created_at')->paginate(5);
        return view('customers.index', compact('customers'));
    }

    // EDIT
    public function edit(User $customer)
    {
        return view('customers.edit', compact('customer'));
    }
    public function update(Request $request, User $customer)
    {
        $request->validate([
            'customer_name' => 'required|string|min:2|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($customer->id, 'id')
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                Rule::unique('users', 'password')->ignore($customer->id, 'id')
            ],
        ]);

        // dd($request->all());
        $customer->update($request->all());
        return redirect()->route('customers.index')->with('success', 'Customer updated successfully');
    }

    // DELETE
    public function delete(User $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'customer deleted successfully');
    }
}

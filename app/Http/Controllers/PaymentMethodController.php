<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethodStatus;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentMethodController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required'],
            'number' => ['required'],
            'account_name' => ['required'],
            'picture' => ['sometimes', 'image']
        ]);

        if ($request->exists('picture')) {
            $data['picture'] = Storage::putFile('payment-methods', $data['picture']);
        }

        $paymentMethod = PaymentMethod::create($data);

        return response()->json(['payment_method' => $paymentMethod]);
    }

    public function index(Request $request)
    {
        $query = PaymentMethod::query();
        if (!$request->user()->isAdmin)
            $query->where('status', PaymentMethodStatus::OPEN->value);
        return response()->json(['payment_methods' => $query->get()]);
    }

    public function toggle(Request $request, paymentMethod $paymentMethod)
    {
        $paymentMethod->update([
            'status' =>
            $paymentMethod->status == PaymentMethodStatus::OPEN->value ? PaymentMethodStatus::CLOSE->value : PaymentMethodStatus::OPEN->value
        ]);
        return response()->json(['payment_method' => $paymentMethod]);
    }

    public function update(Request $request, paymentMethod $paymentMethod)
    {
        $data = $request->validate([
            'name' => ['required'],
            'number' => ['required'],
            'account_name' => ['required'],
            'picture' => ['sometimes', 'image']
        ]);

        if ($request->exists('picture')) {
            $path = $paymentMethod->getRawOriginal('picture');
            if ($path && Storage::exists($path)) Storage::delete($path);
            $data['picture'] = Storage::putFile('payment-methods', $data['picture']);
        }

        $paymentMethod->update($data);

        return response()->json(['payment_method' => $paymentMethod]);
    }

    public function find(Request $request, paymentMethod $paymentMethod)
    {
        return response()->json(['payment_method' => $paymentMethod]);
    }
}

<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\GroceryService;
use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class StoreController extends Controller
{
    /**
     * Create a new StoreController instance.
     */
    public function __construct(
        protected GroceryService $groceryService,
        protected OrderService $orderService
    ) {}

    /**
     * Display the user-facing grocery storefront.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $filters = array_filter(['search' => $search]);

        $groceries = $this->groceryService->getAvailableItems(12, $filters);

        $user = Auth::user();
        $jwtToken = $user ? JWTAuth::fromUser($user) : '';

        return view('store', [
            'groceries' => $groceries,
            'search' => $search,
            'user' => $user,
            'jwtToken' => $jwtToken,
        ]);
    }

    /**
     * Display the order history page.
     */
    public function orders(Request $request): View
    {
        $user = Auth::user();
        $orders = $this->orderService->getUserOrders($user, 10);

        return view('orders', [
            'orders' => $orders,
            'user' => $user,
        ]);
    }
}

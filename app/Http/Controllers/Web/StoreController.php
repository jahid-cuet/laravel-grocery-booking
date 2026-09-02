<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Services\GroceryService;
use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
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

        $groceries = $this->groceryService->getAllPaginated($filters, 12);

        // Fetch or create a default demo customer for the web storefront session
        $demoUser = User::whereHas('role', fn ($q) => $q->where('slug', 'user'))->first();
        $jwtToken = $demoUser ? JWTAuth::fromUser($demoUser) : '';

        return view('store', [
            'groceries' => $groceries,
            'search' => $search,
            'demoUser' => $demoUser,
            'jwtToken' => $jwtToken,
        ]);
    }

    /**
     * Display the order history page.
     */
    public function orders(Request $request): View
    {
        $demoUser = User::whereHas('role', fn ($q) => $q->where('slug', 'user'))->first();
        $orders = $demoUser ? $this->orderService->getUserOrders($demoUser, 10) : new LengthAwarePaginator([], 0, 10);
        $jwtToken = $demoUser ? JWTAuth::fromUser($demoUser) : '';

        return view('orders', [
            'orders' => $orders,
            'demoUser' => $demoUser,
            'jwtToken' => $jwtToken,
        ]);
    }
}

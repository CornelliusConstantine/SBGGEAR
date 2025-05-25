<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockHistory;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->category_id);
            })
            ->when($request->filled('stock_status'), function ($query) use ($request) {
                switch ($request->stock_status) {
                    case 'low':
                        $query->where('stock', '<=', 10);
                        break;
                    case 'out':
                        $query->where('stock', 0);
                        break;
                    case 'available':
                        $query->where('stock', '>', 0);
                        break;
                }
            });

        $products = $query->orderBy('stock', 'asc')
            ->paginate($request->input('per_page', 15));

        return response()->json($products);
    }

    public function adjust(Request $request, Product $product)
    {
        $request->validate([
            'quantity_change' => ['required', 'integer', 'not_in:0'],
            'type' => ['required', 'in:in,out,adjustment'],
            'notes' => ['required', 'string'],
        ]);

        $stockBefore = $product->stock;
        $newStock = $stockBefore + $request->quantity_change;

        if ($newStock < 0) {
            return response()->json([
                'message' => 'Stock cannot be negative',
            ], 422);
        }

        // Update product stock
        $product->update(['stock' => $newStock]);

        // Create stock history
        StockHistory::create([
            'product_id' => $product->id,
            'quantity_change' => $request->quantity_change,
            'stock_before' => $stockBefore,
            'stock_after' => $newStock,
            'type' => $request->type,
            'reference_type' => 'manual',
            'notes' => $request->notes,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Stock adjusted successfully',
            'product' => $product->fresh(),
        ]);
    }

    public function history(Request $request, Product $product)
    {
        $history = $product->stockHistories()
            ->with('creator:id,name')
            ->when($request->filled('type'), function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return response()->json($history);
    }
} 
<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class SalesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $title = 'sales';
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $searchQuery = $request->input('query');
        // dd($startDate, $endDate);
        // Query sales data conditionally
        $sales = Sale::query()
            ->when(
                $startDate,
                function ($query) use ($startDate, $endDate) {
                    return $query->whereBetween('sale_date', [$startDate, $endDate]);
                }
                /* )->when(
                $searchQuery,
                function ($query) use ($searchQuery) {
                    $query->where();
                } */
            )->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $sales->load('customer:id,name', 'saleItems.product:id,name', 'user:id,username')->loadCount('saleItems')
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'customer' => 'required|string|max:255',
            'payment_method' => 'required|string',
            'total' => 'required|numeric',
            'items' => 'required|array',
            'items.*.id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0'
        ]);
        // return $validated;
        DB::beginTransaction();
        try {
            $customer = Customer::firstOrCreate(['name' => $validated['customer']]);
            $sale = Sale::create([
                'customer_id' => $customer->id,
                'number' => rand(111111, 999989),
                'sale_date' => now(),
                'total_amount' => $validated['total'],
                'payment_method' => $validated['payment_method'] ?? 'cash',
                'sold_by' => request()->user()->id
            ]);

            foreach ($validated['items'] as $item) {
                $sale->saleItems()->create([
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'date' => now()->toDateString()
                ]);
            }
            DB::commit();
            // try {
            //     $connector = new WindowsPrintConnector(config('store.printer'));
            //     $printer = new Printer($connector);
            //     $printer->initialize();
            //     $printer->setJustification(Printer::JUSTIFY_CENTER);
            //     $printer->text(config('store.name'));
            //     $printer->feed();
            //     $printer->text(config('store.location'));
            //     $printer->feed();
            //     $printer->text(config('store.phone'));
            //     $printer->feed(2);
            //     $printer->setJustification(Printer::JUSTIFY_LEFT);
            //     $printer->text("Sale #{$sale->number}\n");
            //     $printer->text("Date: {$sale->created_at->format('Y-m-d h:i A')}\n");
            //     // Log::info('items:' . $sale);
            //     $printer->feed();
            //     $printer->setJustification(Printer::JUSTIFY_LEFT);
            //     $printer->text(sprintf(
            //         "%-28s %3s %5s %9s",  // Format specifiers
            //         'Item',                  // Column 1: Product name
            //         'Qty',                   // Column 2: Quantity
            //         'Price',                 // Column 3: Unit price
            //         'SubTotal'               // Column 4: Total price
            //     ));

            //     // Optional separator line
            //     $printer->text("________________________________________________\n");

            //     // Reset justification for numbers
            //     $printer->setJustification(Printer::JUSTIFY_RIGHT);

            //     foreach ($sale->saleItems as $item) {
            //         $printer->text(sprintf(
            //             "%-28s %3d %6.2f %8.2f\n",
            //             substr(strtoupper($item->product->name), 0, 25),
            //             $item->quantity,
            //             $item->price,
            //             $item->total
            //         ));
            //     }
            //     $printer->text("________________________________________________\n");
            //     $printer->setJustification(Printer::JUSTIFY_LEFT);
            //     $printer->text("Total: GHS" . number_format($sale->total_amount, 2, '.', ',') . "\n");
            //     $printer->text("Paid with: " . strtoupper($sale->payment_method ?? 'CASH') . "\n");
            //     $printer->setJustification(Printer::JUSTIFY_CENTER);
            //     $printer->feed();
            //     $printer->text('Thank you for shopping from us.');
            //     $printer->feed(2);
            //     $printer->cut();
            //     $printer->close();
            // } catch (\Exception $e) {
            //     Log::error("Print job failed: " . $e->getMessage());
            // }
            return response()->json([
                'message' => 'success',
                'success' => true,
                'sale' => $sale->fresh('customer:id,name','saleItems', 'user:id,username')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Order checkout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale)
    {
        return $sale->load('saleItems.product');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sale $sale)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        //
    }
    public function print(Sale $sale)
    {
        try {
            $connector = new WindowsPrintConnector(config('store.printer'));
            $printer = new Printer($connector);
            $printer->initialize();
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text(config('store.name'));
            $printer->feed();
            $printer->text(config('store.location'));
            $printer->feed();
            $printer->text(config('store.phone'));
            $printer->feed(2);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Sale #{$sale->number}\n");
            $printer->text("Date: {$sale->created_at->format('Y-m-d h:i A')}\n");
            // Log::info('items:' . $sale);
            $printer->feed();
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text(sprintf(
                "%-28s %3s %5s %9s",  // Format specifiers
                'Item',                  // Column 1: Product name
                'Qty',                   // Column 2: Quantity
                'Price',                 // Column 3: Unit price
                'SubTotal'               // Column 4: Total price
            ));

            // Optional separator line
            $printer->text("________________________________________________\n");

            // Reset justification for numbers
            $printer->setJustification(Printer::JUSTIFY_RIGHT);

            foreach ($sale->saleItems as $item) {
                $printer->text(sprintf(
                    "%-28s %3d %6.2f %8.2f\n",
                    substr(strtoupper($item->product->name), 0, 25),
                    $item->quantity,
                    $item->price,
                    $item->total
                ));
            }
            $printer->text("________________________________________________\n");
            // $printer->feed();
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Total: GHS" . number_format($sale->total_amount, 2, '.', ',') . "\n");
            $printer->text("Paid with: " . strtoupper($sale->payment_method ?? 'CASH') . "\n");
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->feed();
            $printer->text('Thank you for shopping from us.');
            $printer->feed(2);
            $printer->cut();
            $printer->close();
        } catch (\Exception $e) {
            Log::error("Print job failed: " . $e->getMessage());
        } finally {
            return response()->json([
                'message' => 'Reciept has been printed successfully',
                'success' => true
            ]);
        }
    }
    public function downloadSales()
    {
        $date = today()->toDateString();
        // $saleItems = SaleItem::whereDate('date',$date)->get();

        // $saleItems = SaleItem::select('product_name', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(price) as total_price'))
        //     ->whereDate('date', $date) // Filter by date
        //     ->groupBy('product_name')  // Group by product name
        //     ->get();

        $saleItems = SaleItem::with('product.currentPrice') // Load the related product model
            ->select('product_id', 'date', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(price) as total_price'))
            ->whereDate('date', $date) // Filter by date
            ->groupBy('product_id', 'date') // Group by product_id (foreign key)
            ->get();
        $saleItems->load('product.currentPrice');

        // return view('sales.template', compact('saleItems'));
        $pdf = Pdf::loadView('sales.template', compact('saleItems'));

        $file = $pdf->save('sales/' . 'sales_' . date('Y_m_d') . '.pdf', 'public');
        return response()->download(storage_path() . '/app/public/sales/' . 'sales_' . date('Y_m_d') . '.pdf', 'sales_' . date('Y_m_d') . '.pdf', ['content-type' => 'application/pdf']);
    }
}

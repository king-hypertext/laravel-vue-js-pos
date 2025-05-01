<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;


class AppController extends Controller
{
    public function saleItems()
    {
        return Sale::all();
    }
    public function stats()
    {
        $startDate = now()->subDays(7);
        $endDate = now();
        $lastMonth = now()->subDays(7);
        // $products_count = Product::count();
        // $total_sales = number_format(Sale::sum('total_amount'), 2, '.', ',');
        // $today_sales = number_format(Sale::whereSaleDate(today())->sum('total_amount'), 2, '.', ',');
        $customers_count = Customer::count();
        $topSellingProduct = SaleItem::query()
            ->select('products.name', DB::raw('SUM(sale_items.quantity) as total_sold'))
            ->join('products', 'sale_items.product_id', '=', 'products.id') // Join with the products table
            ->whereBetween('sale_items.created_at', [$startDate, $endDate])
            ->groupBy('sale_items.product_id', 'products.name') // Group by both product_id and product name
            ->orderByDesc('total_sold')
            ->limit(10)->get();

        $topSellingProductLastMonth = SaleItem::query()
            ->select('products.name', DB::raw('SUM(sale_items.quantity) as total_sold'))
            ->join('products', 'sale_items.product_id', '=', 'products.id') // Join with the products table
            ->whereBetween('sale_items.created_at', [$lastMonth, $endDate])
            ->groupBy('sale_items.product_id', 'products.name') // Group by both product_id and product name
            ->orderByDesc('total_sold')
            ->first();
        return response()->json([
            'success' => true,
            'data' => [
                'customers_count' => $customers_count,
                'top_selling_products_last_week' => $topSellingProduct,
                'top_selling_product_last_month' => $topSellingProductLastMonth
            ]
        ]);
    }
    // public function print()
    // {
    //     try {
    //         $connector = new WindowsPrintConnector("XP-80C");
    //         // Enter the share name for your USB printer here
    //         // Replace "XP-T80A" with your printer's name in Windows
    //         $printer = new Printer($connector);

    //         // --- Receipt Content ---
    //         $printer->setJustification(Printer::JUSTIFY_CENTER);
    //         $printer->text("MY STORE NAME\n");
    //         $printer->text("123 Main Street\n");
    //         $printer->text("Tel: 123-456-7890\n");
    //         $printer->text("\n");

    //         $printer->setJustification(Printer::JUSTIFY_LEFT);
    //         $printer->text("Order #: 1001\n");
    //         $printer->text("Date: " . date('Y-m-d H:i:s') . "\n");
    //         $printer->text("------------------------------\n");

    //         // Items
    //         $printer->text("ITEM           QTY   PRICE   TOTAL\n");
    //         $printer->text("Apple          2     \$1.50   \$3.00\n");
    //         $printer->text("Banana         3     \$0.50   \$1.50\n");
    //         $printer->text("------------------------------\n");

    //         // Totals
    //         $printer->setJustification(Printer::JUSTIFY_RIGHT);
    //         // $printer->text("Subtotal: \$4.50\n");
    //         // $printer->text("Tax: \$0.50\n");
    //         // $printer->text("Total: \$5.00\n");
    //         // $printer->text("\n");
    //         $items = array(
    //             new item("Example item #1", "4.00"),
    //             new item("Another thing", "3.50"),
    //             new item("Something else", "1.00"),
    //             new item("A final item", "4.45"),
    //         );
    //         $printer->setEmphasis(false);
    //         foreach ($items as $item) {
    //             $printer->text($item);
    //         }
    //         // Footer
    //         $printer->setJustification(Printer::JUSTIFY_CENTER);
    //         $printer->text("Thank you for shopping!\n");
    //         $printer->text("www.mystore.com\n");

    //         // Cut the paper
    //         $printer->cut();

    //         // Close the printer connection
    //         $printer->close();
    //         return response()->json(['status' => 'success', 'message' => 'Receipt printed!']);
    //     } catch (\Exception $e) {
    //         return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
    //     }
    // }
}

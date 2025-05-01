<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\Price;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class InventorySeeder extends Seeder
{
    public function run()
    {
        // Seed 35 products, each with an associated price.
        for ($i = 1; $i <= 35; $i++) {
            $expiryDate = Carbon::now()->addDays(rand(30, 365));

            $product = Product::create([
                'name'          => 'Product ' . $i,
                'quantity'      => rand(10, 100),
                'reorder_level' => rand(5, 20),
                'location'      => 'Aisle ' . rand(1, 5),
                'bar_code'      => 'BAR' . rand(1000, 9999),
                'expiry_date'   => $expiryDate->toDateString(),
                'description'   => 'This is a description for Product ' . $i,
                'brand'         => 'Brand ' . rand(1, 10),
                'category'      => 'Category ' . rand(1, 10),
                'supplier'      => 'Supplier ' . rand(1, 10),
            ]);

            Price::create([
                'cost_price' => rand(50, 150) + (rand(0, 99) / 100),
                'price'      => rand(151, 300) + (rand(0, 99) / 100),
                'product_id' => $product->id,
            ]);
        }

        // Create sales with sale items.
        // Payment methods available.
        $paymentMethods = ['cash', 'momo'];

        // Let’s create 10 sales transactions.
        for ($s = 1; $s <= 10; $s++) {
            $saleDate   = Carbon::now()->subDays(rand(0, 30));
            $saleNumber = 'SALE-' . strtoupper(Str::random(8));

            $faker = Faker::create();

            // Create 35 customers with random data.
            // for ($i = 0; $i < 35; $i++) {
            $customer = Customer::create([
                'name'    => $faker->name,
                'email'   => $faker->unique()->safeEmail,
                'phone'   => $faker->unique()->phoneNumber,
                'address' => $faker->address,
            ]);
            // }
            $sale = Sale::create([
                'customer_id'    => $customer->id, // No customer seeded; field is nullable.
                'number'         => $saleNumber,
                'sale_date'      => $saleDate,
                'total_amount'   => 0, // Temporary value; will update after inserting sale items.
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                'sold_by'        => 'Admin',  // You can use random names or seller information here.
            ]);

            $saleTotal = 0;

            // Each sale gets between 1 and 5 sale items.
            $itemsCount = rand(1, 5);
            for ($i = 0; $i < $itemsCount; $i++) {
                // Choose one random product.
                $product = Product::inRandomOrder()->first();

                // Fetch the price record for the product.
                $priceRecord = Price::where('product_id', $product->id)->first();
                $salePrice = $priceRecord ? $priceRecord->price : 100;

                $quantity = rand(1, 10);

                // Create the sale item. The 'total' column is computed automatically.
                SaleItem::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $product->id,
                    'quantity'   => $quantity,
                    'price'      => $salePrice,
                    'date' => now()
                ]);

                $saleTotal += $salePrice * $quantity;
            }

            // Update the sale with the total amount.
            $sale->update(['total_amount' => $saleTotal]);
        }
    }
}

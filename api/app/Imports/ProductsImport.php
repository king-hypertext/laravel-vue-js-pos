<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithSkipDuplicates;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;
// use PhpOffice\PhpSpreadsheet\Shared\Date;

class ProductsImport implements ToModel, WithValidation, WithHeadingRow, WithSkipDuplicates, WithMapping, WithUpserts
{
    use Importable;
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $supplier = Supplier::firstOrCreate(['name' => $row['Supplier'] ?? 'default']);
        $brand =  Brand::firstOrCreate(['name' => $row['Brand'] ?? 'default']);
        $category = Category::firstOrCreate(['name' => $row['Category'] ?? 'default']);

        $Product = new Product([
            'name' => $row['Product Name'],
            'quantity' => $row['Quantity'],
            'location' => $row['Shell Location'],
            'expiry_date' => /* $row['Expiry Date'] */ now()->addYears(2),
            'description' => $row['Description'],
            'category_id' => $category?->id,
            'supplier_id' => $supplier?->id,
            'brand_id' => $brand?->id,
        ]);
        $Product->save();
        $Product->prices()->create([
            'price' => $row['Price'],
            'cost_price' => $row['Cost Price'],
        ]);
        return $Product;
    }
    public function uniqueBy()
    {
        return 'name';
    }
    public function headingRow(): int
    {
        return 1;
    }
    // public function map($row): array
    // {
    //     // $row['Expiry Date'] = Date::excelToDateTimeObject($row['Expiry Date'] ?? now()->addMonth())->format('Y-m-d');
    //     return $row;
    // }
    public function rules(): array
    {
        return [
            '*.Product Name' => ['required', /* 'unique:products,name', */ 'string', 'max:255'],
            '*.Quantity' => ['required', 'numeric'],
            '*.Price' => ['required'],
            '*.Cost Price' => ['required'],
            // '*.Expiry Date' => ['nullable', 'date'],
            '*.Shell Location' => ['nullable', 'string', 'max:255'],
            '*.Description' => ['nullable', 'string', 'max:255'],
            '*.Supplier' => ['nullable', 'string', 'unique:suppliers,name', 'max:255'],
            '*.Brand' => ['nullable', 'string', 'unique:brands,name', 'max:255'],
            '*.Category' => ['nullable', 'string', 'unique:categories,name', 'max:255']
        ];
    }
    public function map($row): array
    {
        $row['Cost Price'] = is_null($row['Cost Price']) ? 0 : $row['Cost Price'];
        $row['Price'] = is_null($row['Price']) ? 0: $row['Price'];
        $row['Quantity'] = is_null($row['Quantity']) ? 0 : $row['Quantity'];
        return $row;
    }
    // public function messages(): array {
    //     return [

    //     ];
    // }
}

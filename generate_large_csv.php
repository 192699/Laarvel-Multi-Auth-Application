<?php

/**
 * Script to generate a large CSV file with 100,000 products for testing bulk import
 * 
 * Usage: php generate_large_csv.php
 */

$outputFile = 'products_large_import.csv';
$totalProducts = 100000;

// Sample data arrays for generating varied products
$categories = ['Electronics', 'Clothing', 'Furniture', 'Sports', 'Books', 'Toys', 'Home & Garden', 'Automotive', 'Health & Beauty', 'Food & Beverages'];
$descriptions = [
    'High-quality product with excellent features',
    'Premium design and superior performance',
    'Durable and long-lasting construction',
    'Modern style with advanced technology',
    'Eco-friendly and sustainable materials',
    'Professional grade quality',
    'User-friendly and easy to use',
    'Compact and space-saving design',
    'Versatile and multi-purpose functionality',
    'Stylish and contemporary appearance'
];

$productNames = [
    'Pro', 'Elite', 'Premium', 'Standard', 'Deluxe', 'Ultra', 'Max', 'Plus', 'Advanced', 'Classic',
    'Smart', 'Digital', 'Wireless', 'Portable', 'Compact', 'Professional', 'Enterprise', 'Home', 'Office', 'Travel'
];

// Open file for writing
$file = fopen($outputFile, 'w');

if (!$file) {
    die("Error: Could not create file $outputFile\n");
}

// Write CSV header
fputcsv($file, ['name', 'description', 'price', 'image', 'category', 'stock']);

echo "Generating $totalProducts products...\n";
$startTime = microtime(true);

// Generate products
for ($i = 1; $i <= $totalProducts; $i++) {
    // Generate product name
    $namePrefix = $productNames[array_rand($productNames)];
    $nameSuffix = $productNames[array_rand($productNames)];
    $productNumber = str_pad($i, 6, '0', STR_PAD_LEFT);
    $name = "$namePrefix $nameSuffix Product $productNumber";
    
    // Generate description (some products have descriptions, some don't)
    $description = ($i % 3 === 0) ? '' : $descriptions[array_rand($descriptions)] . " - Item #$productNumber";
    
    // Generate price (between 9.99 and 9999.99)
    $price = number_format(rand(999, 999999) / 100, 2, '.', '');
    
    // Generate image (most products don't have images, will use default)
    // Only 10% of products have custom images
    $image = ($i % 10 === 0) ? "products/custom_$productNumber.jpg" : '';
    
    // Generate category
    $category = $categories[array_rand($categories)];
    
    // Generate stock (between 0 and 1000)
    $stock = rand(0, 1000);
    
    // Write row to CSV
    fputcsv($file, [
        $name,
        $description,
        $price,
        $image,
        $category,
        $stock
    ]);
    
    // Progress indicator
    if ($i % 10000 === 0) {
        $elapsed = microtime(true) - $startTime;
        $remaining = ($elapsed / $i) * ($totalProducts - $i);
        echo "Progress: $i/$totalProducts products (" . number_format(($i / $totalProducts) * 100, 1) . "%) - Estimated time remaining: " . number_format($remaining, 0) . " seconds\n";
    }
}

fclose($file);

$endTime = microtime(true);
$totalTime = $endTime - $startTime;
$fileSize = filesize($outputFile);

echo "\n";
echo "✓ Successfully generated $outputFile\n";
echo "  - Total products: " . number_format($totalProducts) . "\n";
echo "  - File size: " . number_format($fileSize / 1024 / 1024, 2) . " MB\n";
echo "  - Generation time: " . number_format($totalTime, 2) . " seconds\n";
echo "\n";
echo "You can now use this file to test the bulk import functionality!\n";
echo "Upload it via: /admin/products/import\n";


<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExportShopifyProducts extends Command
{
    protected $signature = 'shopify:export-products';
    protected $description = 'Export Shopify products to CSV file';

    private $BATCH_SIZE = 5;

    public function handle()
    {
        $this->info('Starting product export...');

        $shop = User::first();
        $products = [];
        $hasNextPage = true;
        $cursor = null;

        while ($hasNextPage) {
            $query = $this->buildQuery($cursor);
            $response = $shop->api()->graph($query);

            foreach ($response['body']['data']['products']['edges'] as $edge) {
                $products[] = [
                    $edge['node']['id'],
                    $edge['node']['title'],
                    $edge['node']['status'],
                ];
            }

            $pageInfo = $response['body']['data']['products']['pageInfo'];
            $hasNextPage = $pageInfo['hasNextPage'];
            $cursor = $pageInfo['endCursor'];
        }

        // Create CSV file
        $csvPath = 'products.csv';
        $handle = fopen(Storage::path($csvPath), 'w');

        // Add headers
        fputcsv($handle, ['ID', 'Title', 'Status']);

        // Add product data
        foreach ($products as $product) {
            fputcsv($handle, $product);
        }

        fclose($handle);

        $this->info(sprintf('Export complete! %d products exported to products.csv', count($products)));
        return 0;
    }

    private function buildQuery($cursor = null)
    {
        $after = $cursor ? 'after: "' . $cursor . '",' : '';
        return <<<QUERY
        {
            products(first: $this->BATCH_SIZE, $after) {
                edges {
                    node {
                        id
                        title
                        status
                    }
                }
                pageInfo {
                    hasNextPage
                    endCursor
                }
            }
        }
        QUERY;
    }

}

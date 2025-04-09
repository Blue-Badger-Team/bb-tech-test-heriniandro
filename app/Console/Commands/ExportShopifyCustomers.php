<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Auth;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Osiset\ShopifyApp\Contracts\ShopModel;

class ExportShopifyCustomers extends Command
{
    protected $signature = 'shopify:export-customers';
    protected $description = 'Export Shopify customers to CSV file';

    private $BATCH_SIZE = 2;

    public function handle()
    {
        $this->info('Starting customer export...');

        $shop = User::first();
        $customers = [];
        $hasNextPage = true;
        $cursor = null;

        while ($hasNextPage) {
            $query = $this->buildQuery($cursor);
            $response = $shop->api()->graph($query);

            foreach ($response['body']['data']['customers']['edges'] as $edge) {
                $customers[] = [
                    $edge['node']['firstName'],
                    $edge['node']['lastName'],
                    $edge['node']['email'],
                ];
            }

            $pageInfo = $response['body']['data']['customers']['pageInfo'];
            $hasNextPage = $pageInfo['hasNextPage'];
            $cursor = $pageInfo['endCursor'];
        }

        // Create CSV file
        $csvPath = 'customers.csv';
        $handle = fopen(Storage::path($csvPath), 'w');

        // Add headers
        fputcsv($handle, ['First Name', 'Last Name', 'Email']);

        // Add customer data
        foreach ($customers as $customer) {
            fputcsv($handle, $customer);
        }

        fclose($handle);

        $this->info(sprintf('Export complete! %d customers exported to customers.csv', count($customers)));
        return 0;
    }

    private function buildQuery($cursor = null)
    {
        $after = $cursor ? 'after: "' . $cursor . '",' : '';
        return <<<QUERY
        {
            customers(first: $this->BATCH_SIZE, $after) {
                edges {
                    node {
                        firstName
                        lastName
                        email
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

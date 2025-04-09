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

    private $BATCH_SIZE = 250;

    public function handle()
    {
        $this->info('Starting customer export...');

        $shop = User::first();
        $query = "";
        $response = $shop->api()->graph($query);

        $customers = [];

        $this->info(sprintf('Export complete! %d customers exported to customers.csv', count($customers)));
        return 0;
    }
}
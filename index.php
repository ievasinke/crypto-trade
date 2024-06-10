<?php
require_once 'vendor/autoload.php';

use App\Wallet;
use GuzzleHttp\Client;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Using https://coinmarketcap.com/ API, create an application that allow you to
 * - List top crypto currencies
 * - [OPTIONAL] search crypto currency by its ticking symbol
 * - Purchase crypto currency using virtual money (start with 1000$ as base)
 * - Sell crypto currency
 * - Display current state of your wallet
 * (based on transaction history, that is saved in .json file)
 * - Display transaction list, what trades you have made
 */

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();
$dotenv->required([
    'CRYPTO_API_KEY',
]);

if (empty('data/crypto.json')) {
    $client = new Client();
    $baseUrl = 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/listings/latest';
    $parameters = [
        'start' => '1',
        'limit' => '20',
        'convert' => 'USD'
    ];

    $response = $client->request(
        'GET',
        $baseUrl,
        [
            'query' => $parameters,
            'headers' => [
                'Accepts' => 'application/json',
                'X-CMC_PRO_API_KEY' => $_ENV['CRYPTO_API_KEY']
            ]
        ]
    );
    $response->getStatusCode();

    file_put_contents('data/crypto.json', $response->getBody());
}

$resource = json_decode(file_get_contents('data/crypto.json'));
$cryptoCurrencies = $resource->data;


while (true) {
    $outputTasks = new ConsoleOutput();
    $tableActivities = new Table($outputTasks);
    $tableActivities
        ->setHeaders(['Index', 'Action'])
        ->setRows([
            ['1', 'Show list of top currencies'],
            ['2', 'Wallet'],
            ['3', 'Sell'],
            ['4', 'Buy'],
            ['5', 'Display transaction list'], //based on transaction history, that is saved in .json file
            ['0', 'Exit'],
        ])
        ->render();
    $action = (int)readline("Enter the index of the action: ");

    if ($action === 0) {
        break;
    }

    switch ($action) {
        case 1:
            $outputCrypto = new ConsoleOutput();
            $tableCryptoCurrencies = new Table($outputCrypto);
            $tableCryptoCurrencies
                ->setHeaders(['Index', 'Name', 'Symbol', 'Price']);
            $rows = (array_map(function (int $index, stdClass $cryptoCurrency): array {
                return [
                    $index + 1,
                    $cryptoCurrency->name,
                    $cryptoCurrency->symbol,
                    new TableCell(
                        number_format($cryptoCurrency->quote->USD->price, 4),
                        ['style' => new TableCellStyle(['align' => 'right',])]
                    ),

                ];
            }, array_keys($cryptoCurrencies), $cryptoCurrencies));

            $tableCryptoCurrencies->setRows($rows);
            $tableCryptoCurrencies->setStyle('box-double');
            $tableCryptoCurrencies->render();
            break;
        case 2: //Wallet
            $wallet = new Wallet();
            echo $wallet->getBalance() . PHP_EOL;
            break;
        case 3: //Sell
            break;
        case 4: //Buy
            break;
        case 5: //Display transaction list
            break;
        default:
            break;


    }
}

<?php

namespace App\Services\Web3;

use App\Models\Chain;
use App\Exceptions\Web3Exception;
use App\Services\BaseService;
use Illuminate\Support\Facades\Redis;
use RenokiCo\LaravelWeb3\Web3;

/**
 * @method static self getInstance()
 */
class Bsc extends BaseService implements Web3Template
{
    protected  $abi = '[{"inputs":[{"internalType":"string","name":"_name","type":"string"},{"internalType":"string","name":"_symbol","type":"string"},{"internalType":"uint8","name":"_decimals","type":"uint8"},{"internalType":"uint256","name":"_mintAmount","type":"uint256"}],"stateMutability":"nonpayable","type":"constructor"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"owner","type":"address"},{"indexed":true,"internalType":"address","name":"spender","type":"address"},{"indexed":false,"internalType":"uint256","name":"value","type":"uint256"}],"name":"Approval","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"from","type":"address"},{"indexed":true,"internalType":"address","name":"to","type":"address"},{"indexed":false,"internalType":"uint256","name":"value","type":"uint256"}],"name":"Transfer","type":"event"},{"inputs":[{"internalType":"address","name":"owner","type":"address"},{"internalType":"address","name":"spender","type":"address"}],"name":"allowance","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"spender","type":"address"},{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"approve","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"account","type":"address"}],"name":"balanceOf","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"decimals","outputs":[{"internalType":"uint8","name":"","type":"uint8"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"spender","type":"address"},{"internalType":"uint256","name":"subtractedValue","type":"uint256"}],"name":"decreaseAllowance","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"spender","type":"address"},{"internalType":"uint256","name":"addedValue","type":"uint256"}],"name":"increaseAllowance","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"name","outputs":[{"internalType":"string","name":"","type":"string"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"symbol","outputs":[{"internalType":"string","name":"","type":"string"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"totalSupply","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"recipient","type":"address"},{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"transfer","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"sender","type":"address"},{"internalType":"address","name":"recipient","type":"address"},{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"transferFrom","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"}]';

    /**
     * web3 connection
     *
     * @reference https://github.com/renoki-co/laravel-web3#multiple-connections
     * @var Web3
     */
    protected $bscConnection;

    public function __construct()
    {
        parent::__construct();
        $this->__initWeb3Connection();
    }

    /**
     * 初始化 web3 链接对象
     *
     * @author Steven
     *
     * @return void
     */
    protected function __initWeb3Connection()
    {
        if (empty($this->bscConnection)) {
            $config = config('web3.connections.bsc_testnet');
            $chainConfig = Chain::query()->find(Chain::CHAIN_BSC);
            //设置地址和超时
            if (!empty($chainConfig)) {
                $config['host'] = $chainConfig->http_url;
                $config['timeout'] = 10;
            }
            $this->bscConnection = new Web3('db-connection', $config);
        }
    }

    //获取nonce
    public function getNonce(string $account): string
    {
        $nonce = "";
        try {
            $this->bscConnection->eth()->getTransactionCount($account, 'pending', function ($err, $res) use (&$nonce) {
                if ($err != null) {
                    throw $err;
                }
                $nonce = $res->toString();
            });
            return $nonce;
        } catch (\Exception $e) {
            sendTelegram('default', 'bsc查询nonce出错', $e);
        }
        return $nonce;
    }

    //获取gas_price
    public function getGasPrice():string
    {
        $bsc_gas_price = "";
        try {
            $redis = Redis::connection("default");
            $bsc_gas_price = $redis->get("bsc_gas_price");
            if ($bsc_gas_price) {
                return $bsc_gas_price;
            }
        } catch (\Exception $e) {
            sendTelegram('default', 'bsc获取gasPrice出错', $e);
        }
        return $bsc_gas_price;
    }

    //获取blockNum
    public function blockNumber(): string
    {
        $blockNum = "";
        try {
            $this->bscConnection->eth()->blockNumber(function ($err, $res) use (&$blockNum) {
                if ($err != null) {
                    throw $err;
                }
                $blockNum = $res->toString();
            });
        } catch (\Exception $e) {
            sendTelegram('default', 'bsc查询最新区块出错', $e);
        }
        return $blockNum;
    }

    //获取gas_limit
    public function getGasLimit(string $account): string
    {
        $estimateGas = 0;
        $this->bscConnection->eth()->estimateGas(["from" => $account], function ($err, $res) use (&$estimateGas) {
            if ($err != null) {
                throw new Web3Exception($err);
            }
            $estimateGas = $res->toString();
        });
        return $estimateGas;
    }

    //余额查询
    public function getBalance(string $queryAddress): string
    {
        $balance = '';
        try {
            $this->bscConnection->eth()->getBalance($queryAddress, function($err, $res) use (&$balance) {
                if ($err != null) {
                    throw $err;
                }
                $balance = $res->toString();
            });
        } catch (\Exception $e) {
            sendTelegram('default', 'bsc合约查询出错', $e);
        }
        return $balance;
    }
    //合约余额查询
    public function contractBalanceOf(string $contractAddress, string $queryAddress, $blockId = 'latest'): string
    {
        $currentBalance = '';
        try {
            $contract = $this->bscConnection->contract($this->abi, $blockId);
            $contract->at($contractAddress)
                ->call('balanceOf', $queryAddress, function ($err, $res) use (&$currentBalance) {
                    if ($err != null) {
                        throw $err;
                    }
                    $currentBalance = $res[0]->toString();
                });
        } catch (\Exception $e) {
            sendTelegram('default', 'bsc合约查询出错', $e);
        }
        return  $currentBalance;
    }
    //合约币名
    public function contractSymbol(string $contractAddress,bool $send = true): string
    {
        $symbol = '';
        try {
            $contract =$this->bscConnection->contract($this->abi);
            $contract->at($contractAddress)
                ->call('symbol', function ($err, $res) use (&$symbol) {
                    if ($err != null) {
                        throw $err;
                    }
                    $symbol = $res[0];
                });
        } catch (\Exception $e) {
            if($send){
                sendTelegram('default', 'bsc合约查询出错', $e);
            }
        }
        return $symbol;
    }
    //合约名
    public function contractName(string $contractAddress): string
    {
        $name = '';
        try {
            $contract =$this->bscConnection->contract($this->abi);
            $contract->at($contractAddress)
                ->call('name', function ($err, $res) use (&$name) {
                    if ($err != null) {
                        throw $err;
                    }
                    $name = $res[0];
                });
        } catch (\Exception $e) {
            sendTelegram('default', 'bsc合约查询出错', $e);
        }
        return $name;
    }
    //发行量
    public function contractTotalSupply(string $contractAddress): string
    {
        $totalSupply = '';
        try {
            $contract =$this->bscConnection->contract($this->abi);
            $contract->at($contractAddress)
                ->call('totalSupply', function ($err, $res) use (&$totalSupply) {
                    if ($err != null) {
                        throw $err;
                    }
                    $totalSupply = $res[0]->toString();
                });
        } catch (\Exception $e) {
            sendTelegram('default', 'bsc合约查询出错', $e);
        }
        return $totalSupply;
    }
    //合约精度查询
    public function contractDecimals(string $contractAddress,bool $send = true): string
    {
        $decimals = '';
        try {
            $contract =$this->bscConnection->contract($this->abi);
            $contract->at($contractAddress)
                ->call('decimals', function ($err, $res) use (&$decimals) {
                    if ($err != null) {
                        throw $err;
                    }
                    $decimals = $res[0]->toString() ?? '0';
                });
        } catch (\Exception $e) {
            if($send){
                sendTelegram('default', 'bsc合约查询出错', $e);
            }
        }
        return $decimals;
    }
}
